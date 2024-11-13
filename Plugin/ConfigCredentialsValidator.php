<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Pay\Plugin;

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

class ConfigCredentialsValidator
{
    public const CONFIG_PREFIX = 'groups/amazon_pay/groups/credentials/fields/';

    public const XML_PATH_ACTIVE = self::CONFIG_PREFIX . 'active_v2/value';
    public const XML_PATH_ACTIVE_INHERIT = self::CONFIG_PREFIX . 'active_v2/inherit';
    public const XML_PATH_PRIVATE_KEY_PEM = self::CONFIG_PREFIX . 'private_key_pem/value';
    public const XML_PATH_PRIVATE_KEY_TEXT = self::CONFIG_PREFIX . 'private_key_text/value';
    public const XML_PATH_PRIVATE_KEY_SELECTED = self::CONFIG_PREFIX . 'private_key_selected/value';
    public const XML_PATH_PRIVATE_KEY_SELECTOR = self::CONFIG_PREFIX . 'private_key_selector/value';
    public const XML_PATH_PUBLIC_KEY_ID = self::CONFIG_PREFIX . 'public_key_id/value';
    public const XML_PATH_STORE_ID = self::CONFIG_PREFIX . 'store_id/value';
    public const XML_PATH_PAYMENT_REGION = self::CONFIG_PREFIX . 'payment_region/value';
    public const XML_PATH_SANDBOX = self::CONFIG_PREFIX . 'sandbox/value';

    public const STORE_VIEW_SCOPE_CODE = 'stores';
    public const WEBSITE_SCOPE_CODE = 'websites';
    public const DEFAULT_SCOPE_CODE = 'default';

    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    protected $amazonConfig;

    /**
     * @var \Amazon\Pay\Client\ClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var array */
    protected $scopeTree;

    /** @var string */
    protected $parentScope;

    /** @var string|int */
    protected $parentScopeCode;

    /**
     * ConfigCredentialsValidator constructor
     *
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Pay\Client\ClientFactoryInterface $clientFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Amazon\Pay\Client\ClientFactoryInterface $clientFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->clientFactory = $clientFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;

        $this->scopeTree = $this->getScopeTree();
    }

    /**
     * Validate Amazon config if on payment section and module is active
     *
     * @param Config $subject
     * @return boolean
     */
    protected function isApplicable(Config $subject)
    {
        $result = $subject->getSection() === 'payment';
        $active = false;
        if ($result) {
            $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
            $scopeCode = $subject->getScopeCode();

            $active = $subject->getData(self::XML_PATH_ACTIVE);
            if ($active === null) {
                $active = $this->amazonConfig->isActive($scope, $scopeCode);
            }
        }
        return $active;
    }

    /**
     * True if module was switched to 'Enabled' from 'Disabled'
     *
     * @param Config $subject
     * @return boolean
     */
    protected function isUpdatedActive(Config $subject)
    {
        $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
        $scopeCode = $subject->getScopeCode();

        $newValue = $subject->getData(self::XML_PATH_ACTIVE);
        $oldValue = $this->amazonConfig->isActive($scope, $scopeCode);

        return $newValue && !$oldValue;
    }

    /**
     * True if store/client ID was changed
     *
     * @param Config $subject
     * @return boolean
     */
    protected function isUpdatedStoreId(Config $subject)
    {
        $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
        $scopeCode = $subject->getScopeCode();

        $newValue = $subject->getData(self::XML_PATH_STORE_ID);
        $oldValue = $this->amazonConfig->getClientId($scope, $scopeCode);

        return $newValue !== $oldValue;
    }

    /**
     * Gets store tree so we can traverse the hierarchy
     *
     * @return array
     */
    protected function getScopeTree()
    {
        $tree = [self::WEBSITE_SCOPE_CODE => []];

        $websites = $this->storeManager->getWebsites();

        /* @var $website \Magento\Store\Model\Website */
        foreach ($websites as $website) {
            $tree[self::WEBSITE_SCOPE_CODE][$website->getId()] = [self::STORE_VIEW_SCOPE_CODE => []];

            /* @var $store \Magento\Store\Model\Store */
            foreach ($website->getStores() as $store) {
                $tree[self::WEBSITE_SCOPE_CODE][$website->getId()][self::STORE_VIEW_SCOPE_CODE][] = $store->getId();
            }
        }

        return $tree;
    }

    /**
     * Get parent scope code
     *
     * @param int|string $storeId
     * @return int|string
     */
    protected function findParentScopeCode($storeId)
    {
        foreach ($this->scopeTree[self::WEBSITE_SCOPE_CODE] as $websiteId => $website) {
            foreach ($website[self::STORE_VIEW_SCOPE_CODE] as $store) {
                if ($store == $storeId) {
                    return $websiteId;
                }
            }
        }
    }

    /**
     * Get parent scope
     *
     * @param string $scope
     * @return string
     */
    private function getParentScope($scope)
    {
        if (!isset($this->parentScope)) {
            if ($scope == self::STORE_VIEW_SCOPE_CODE) {
                $this->parentScope = self::WEBSITE_SCOPE_CODE;
            } else {
                $this->parentScope = self::DEFAULT_SCOPE_CODE;
            }
        }

        return $this->parentScope;
    }

    /**
     * Get parent scope code
     *
     * @param int|string $storeId
     * @return int|string
     */
    private function getParentScopeCode($storeId)
    {
        if (!isset($this->parentScopeCode)) {
            $this->parentScopeCode = $this->findParentScopeCode($storeId);
        }

        return $this->parentScopeCode;
    }

    /**
     * Return all updated config values
     *
     * @param Config $subject
     * @return array
     */
    protected function getUpdatedConfig(Config $subject)
    {
        $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
        $scopeCode = $subject->getScopeCode();
        $parentScope = $this->getParentScope($scope);
        $parentScopeCode = $this->getParentScopeCode($subject->getStore());

        $privateKey = $this->readPrivateKey($subject);

        $publicKeyId = $subject->getData(self::XML_PATH_PUBLIC_KEY_ID);
        if ($publicKeyId && $publicKeyId === $this->amazonConfig->getPublicKeyId($scope, $scopeCode)) {
            $publicKeyId = null;
        } elseif ($subject->getData(str_replace('value', 'inherit', self::XML_PATH_PUBLIC_KEY_ID))) {
            $publicKeyId = $this->amazonConfig->getPublicKeyId($parentScope, $parentScopeCode);
        }

        $paymentRegion = $subject->getData(self::XML_PATH_PAYMENT_REGION);
        if ($paymentRegion && $paymentRegion === $this->amazonConfig->getPaymentRegion($scope, $scopeCode)) {
            $paymentRegion = null;
        } elseif ($subject->getData(str_replace('value', 'inherit', self::XML_PATH_PAYMENT_REGION))) {
            $paymentRegion = $this->amazonConfig->getPaymentRegion($parentScope, $parentScopeCode);
        }

        $sandbox = $subject->getData(self::XML_PATH_SANDBOX);
        if ($sandbox !== null) {
            $sandbox = boolval($sandbox) !== $this->amazonConfig->isSandboxEnabled($scope, $scopeCode) ?
                boolval($sandbox) : null;
        } elseif ($subject->getData(str_replace('value', 'inherit', self::XML_PATH_SANDBOX))) {
            $sandbox = $this->amazonConfig->isSandboxEnabled($parentScope, $parentScopeCode);
        }

        return array_filter([
            'private_key' => $privateKey,
            'public_key_id' => $publicKeyId,
            'region' => $paymentRegion,
            'sandbox' => $sandbox,
        ]);
    }

    /**
     * True if config gets value from website/default
     *
     * @param Config $subject
     * @param string $path
     * @return bool
     */
    private function isInherited($subject, $path)
    {
        return $subject->getData(str_replace('value', 'inherit', $path));
    }

    /**
     * Get private key as string
     *
     * @param Config $subject
     * @return string
     */
    private function readPrivateKey($subject)
    {
        $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORES;
        $scopeCode = $subject->getScopeCode();

        $privateKey = '';
        $privateKeyArray['name'] = '';

        // check for inherited value
        $keyMethod = $subject->getData(self::XML_PATH_PRIVATE_KEY_SELECTED);
        if ($this->isInherited($subject, self::XML_PATH_PRIVATE_KEY_SELECTED)) {
            $keyMethod = $this->amazonConfig->getPrivateKeySelected(
                $this->getParentScope($scope),
                $this->getParentScopeCode($subject->getStore())
            );
        }

        if (!in_array($keyMethod, ['text', 'pem'])) {
            $keyMethod = 'text';
        }

        if (($keyMethod == 'pem' && $this->isInherited($subject, self::XML_PATH_PRIVATE_KEY_PEM)) ||
            ($keyMethod == 'text' && $this->isInherited($subject, self::XML_PATH_PRIVATE_KEY_TEXT))
        ) {
            return $this->amazonConfig->getPrivateKey(
                $this->getParentScope($scope),
                $this->getParentScopeCode($subject->getStore())
            );
        }

        // check for pem file presence first
        if ($subject->getData(self::XML_PATH_PRIVATE_KEY_PEM)) {
            $privateKeyArray = $subject->getData(self::XML_PATH_PRIVATE_KEY_PEM);
        }
        // if pem file present
        if (!empty($privateKeyArray['name'])) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $privateKey = file_get_contents($privateKeyArray['tmp_name']);
            $pattern = '/^-----BEGIN (RSA )?PRIVATE KEY-----.*-----END (RSA )?PRIVATE KEY-----$/s';
            if (!preg_match($pattern, $privateKey)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid key'));
            }
        } else {
            // no file present, check for text field
            if ($subject->getData(self::XML_PATH_PRIVATE_KEY_TEXT) &&
                $subject->getData(self::XML_PATH_PRIVATE_KEY_TEXT) !== '------'
            ) {
                $privateKey = $subject->getData(self::XML_PATH_PRIVATE_KEY_TEXT);
            }
        }
        if ($privateKey && (
                preg_match('/^\*+$/', $privateKey) ||
                $privateKey === $this->amazonConfig->getPrivateKey($scope, $scopeCode))
        ) {
            $privateKey = null;
        } elseif ($privateKey == '') {
            throw new ValidatorException(new Phrase('Please provide a Private Key'));
        }

        return $privateKey;
    }

    /**
     * Determine if config is valid by issuing a dummy call to Amazon Pay API
     *
     * @param Config $subject
     * @param array $config
     * @return $this
     * @throws ValidatorException
     */
    protected function validateConfig(Config $subject, array $config = [])
    {
        $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
        $scopeCode = $subject->getScopeCode();
        $storeId = $subject->getStore();

        if ($subject->getData(str_replace('value', 'inherit', self::XML_PATH_STORE_ID))) {
            $storeId = $this->amazonConfig->getClientId(
                $this->getParentScope($scope),
                $this->getParentScopeCode($storeId)
            );
        } else {
            $storeId = $subject->getData(self::XML_PATH_STORE_ID);
            if ($storeId === null) {
                $storeId = $this->amazonConfig->getClientId($scope, $scopeCode);
            }
        }

        $client = $this->clientFactory->create($scopeCode, $scope, $config);
        $response = $client->createCheckoutSession([
            'webCheckoutDetails' => [
                'checkoutReviewReturnUrl' => $this->amazonConfig->getCheckoutReviewReturnUrl(),
            ],
            'storeId' => $storeId,
            'platformId' => $this->amazonConfig->getPlatformId(),
        ], [
            'x-amz-pay-idempotency-key' => uniqid(),
        ]);

        if (!in_array($response['status'], [200, 201])) {
            $data = json_decode($response['response'], true);
            if ($data['reasonCode'] == 'InvalidRequestSignature') {
                throw new ValidatorException(
                    new Phrase('Unable to sign request, is your RSA private key valid?')
                );
            }
            throw new ValidatorException(new Phrase($data['message']));
        }
        return $this;
    }

    /**
     * Convenience function to deactivate module and remove field inheritance if validation fails
     *
     * @param Config $subject
     * @param string $path
     * @param mixed $value
     * @return void
     */
    protected function setDataByPath(Config $subject, $path, $value)
    {
        $data = $subject->getData();
        $target = &$data;
        foreach (explode('/', $path) as $key) {
            if (!isset($target[$key])) {
                unset($target);
                break;
            }
            $target = &$target[$key];
        }
        if (isset($target)) {
            $target = $value;
        }
        $subject->setData($data);
    }

    /**
     * Validate updated module config, deactivate module for scope if invalid
     *
     * @param Config $subject
     * @return null
     */
    public function beforeSave(Config $subject)
    {
        try {
            if ($this->isApplicable($subject)) {
                $subject->load();
                $config = $this->getUpdatedConfig($subject);
                if (!empty($config) || $this->isUpdatedActive($subject) || $this->isUpdatedStoreId($subject)) {
                    $this->validateConfig($subject, $config);
                }
            }
        } catch (\Exception $e) {
            $this->setDataByPath($subject, self::XML_PATH_ACTIVE_INHERIT, false);
            $this->setDataByPath($subject, self::XML_PATH_ACTIVE, false);
            $this->messageManager->addErrorMessage(__('Failed to enable Amazon Pay: %1', $e->getMessage()));
        }
        return null;
    }
}
