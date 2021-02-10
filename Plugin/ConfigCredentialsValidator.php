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
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

class ConfigCredentialsValidator
{
    const XML_PATH_API_VERSION = 'groups/amazon_pay/fields/api_version/value';
    const XML_PATH_ACTIVE = 'groups/amazon_pay/groups/credentials/fields/active_v2/value';
    const XML_PATH_ACTIVE_INHERIT = 'groups/amazon_pay/groups/credentials/fields/active_v2/inherit';
    const XML_PATH_PRIVATE_KEY = 'groups/amazon_pay/groups/credentials/fields/private_key/value';
    const XML_PATH_PUBLIC_KEY_ID = 'groups/amazon_pay/groups/credentials/fields/public_key_id/value';
    const XML_PATH_STORE_ID = 'groups/amazon_pay/groups/credentials/fields/store_id/value';
    const XML_PATH_PAYMENT_REGION = 'groups/amazon_pay/groups/credentials/fields/payment_region/value';
    const XML_PATH_SANDBOX = 'groups/amazon_pay/groups/credentials/fields/sandbox/value';

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

    public function __construct(
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Amazon\Pay\Client\ClientFactoryInterface $clientFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->clientFactory = $clientFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Config $subject
     * @return boolean
     */
    protected function isApplicable(Config $subject)
    {
        $result = $subject->getSection() === 'payment';
        if ($result) {
            $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
            $scopeCode = $subject->getScopeCode();

            $active = $subject->getData(self::XML_PATH_ACTIVE);
            if ($active === null) {
                $active = $this->amazonConfig->isActive($scope, $scopeCode);
            }

            $apiVersion = $subject->getData(self::XML_PATH_API_VERSION);
            if ($apiVersion === null) {
                $apiVersion = $this->amazonConfig->getApiVersion($scope, $scopeCode);
            }

            $result = $active && $apiVersion === '2';
        }
        return $result;
    }

    /**
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
     * @param Config $subject
     * @return array
     */
    protected function getUpdatedConfig(Config $subject)
    {
        $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
        $scopeCode = $subject->getScopeCode();

        $privateKey = $subject->getData(self::XML_PATH_PRIVATE_KEY);
        if ($privateKey && (
                preg_match('/^\*+$/', $privateKey) ||
                $privateKey === $this->amazonConfig->getPrivateKey($scope, $scopeCode))
        ) {
            $privateKey = null;
        }
        $publicKeyId = $subject->getData(self::XML_PATH_PUBLIC_KEY_ID);
        if ($publicKeyId && $publicKeyId === $this->amazonConfig->getPublicKeyId($scope, $scopeCode)) {
            $publicKeyId = null;
        }
        $paymentRegion = $subject->getData(self::XML_PATH_PAYMENT_REGION);
        if ($paymentRegion && $paymentRegion === $this->amazonConfig->getPaymentRegion($scope, $scopeCode)) {
            $paymentRegion = null;
        }
        $sandbox = $subject->getData(self::XML_PATH_SANDBOX);
        if ($sandbox !== null) {
            $sandbox = boolval($sandbox) !== $this->amazonConfig->isSandboxEnabled($scope, $scopeCode) ?
                boolval($sandbox) : null;
        }

        return array_filter([
            'private_key' => $privateKey,
            'public_key_id' => $publicKeyId,
            'region' => $paymentRegion,
            'sandbox' => $sandbox,
        ]);
    }

    /**
     * @param Config $subject
     * @param array $config
     * @return $this
     * @throws ValidatorException
     */
    protected function validateConfig(Config $subject, array $config = [])
    {
        $scope = $subject->getScope() ?: ScopeInterface::SCOPE_STORE;
        $scopeCode = $subject->getScopeCode();

        $storeId = $subject->getData(self::XML_PATH_STORE_ID);
        if ($storeId === null) {
            $storeId = $this->amazonConfig->getClientId($scope, $scopeCode);
        }

        $client = $this->clientFactory->create($scopeCode, $scope, $config);
        $response = $client->createCheckoutSession([
            'webCheckoutDetails' => [
                'checkoutReviewReturnUrl' => $this->amazonConfig->getCheckoutReviewUrl(),
            ],
            'storeId' => $storeId,
            'platformId' => $this->amazonConfig->getPlatformId(),
        ], [
            'x-amz-pay-idempotency-key' => uniqid(),
        ]);

        if (!in_array($response['status'], [200, 201])) {
            $data = json_decode($response['response'], true);
            throw new ValidatorException(new Phrase($data['message']));
        }
        return $this;
    }

    /**
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
        return $this;
    }

    /**
     * @param Config $subject
     * @return null
     */
    public function beforeSave(Config $subject)
    {
        try {
            if ($this->isApplicable($subject)) {
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
