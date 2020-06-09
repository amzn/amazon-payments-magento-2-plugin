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

namespace Amazon\PayV2\Model;

use Magento\Store\Model\ScopeInterface;

class AmazonConfig
{
    const LANG_DE = 'de_DE';
    const LANG_FR = 'fr_FR';
    const LANG_ES = 'es_ES';
    const LANG_IT = 'it_IT';
    const LANG_JA = 'ja_JP';
    const LANG_UK = 'en_GB';
    const LANG_US = 'en_US';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amazon\PayV2\Helper\ClientIp
     */
    private $clientIpHelper;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * AmazonConfig constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Amazon\PayV2\Helper\ClientIp $clientIpHelper
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amazon\PayV2\Helper\ClientIp $clientIpHelper,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\App\State $appState
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->clientIpHelper = $clientIpHelper;
        $this->localeResolver = $localeResolver;
        $this->appState = $appState;
    }

    /**
     * Is PayV2 enabled?
     *
     * @return bool
     */
    public function isEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        if ($this->getApiVersion() != '2') {
            return false;
        }

        if (!$this->clientIpHelper->clientHasAllowedIp()) {
            return false;
        }

        if (!$this->isCurrentCurrencySupportedByAmazon($scope, $scopeCode)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            'payment/amazon_payment_v2/active',
            $scope,
            $scopeCode
        );
    }

    /**
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return string
     */
    public function getApiVersion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/api_version',
            $scope,
            $scopeCode
        );
    }

    /**
     * @return string
     */
    public function getRegion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->getPaymentRegion($scope, $scopeCode);
    }

    /**
     * @param string $scope
     * @param null $scopeCode
     * @return string
     */
    public function getLanguage($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $paymentRegion = $this->getRegion($scope, $scopeCode);
        @list($lang, $region) = explode('_', $this->localeResolver->getLocale());
        switch ($lang) {
            case 'de':
                $result = self::LANG_DE;
                break;
            case 'fr':
                $result = self::LANG_FR;
                break;
            case 'es':
                $result = self::LANG_ES;
                break;
            case 'it':
                $result = self::LANG_IT;
                break;
            case 'ja':
                $result = self::LANG_JA;
                break;
            case 'en':
                $result = $paymentRegion == 'us' ? self::LANG_US : self::LANG_UK;
                break;
        }
        if (!isset($result)) {
            switch ($paymentRegion) {
                case 'jp':
                    $result = self::LANG_JA;
                    break;
                case 'us':
                    $result = self::LANG_US;
                    break;
                default:
                    $result = self::LANG_UK;
                    break;
            }
        }
        return $result;
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @return bool
     */
    public function isCurrentCurrencySupportedByAmazon($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->getCurrentCurrencyCode() == $this->getCurrencyCode($scope, $scopeCode);
    }

    /**
     * @return string
     */
    public function getCurrencyCode($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $paymentRegion = $this->getPaymentRegion($scope, $scopeCode);

        $currencyCodeMap = [
            'de' => 'EUR',
            'uk' => 'GBP',
            'us' => 'USD',
            'jp' => 'JPY'
        ];

        return array_key_exists($paymentRegion, $currencyCodeMap) ? $currencyCodeMap[$paymentRegion] : '';
    }

    /**
     * Is debug logging enabled?
     *
     * @return bool
     */
    public function isLoggingEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (bool)$this->scopeConfig->getValue(
            'payment/amazon_payment/logging',
            $scope,
            $scopeCode
        );
    }

    /**
     * Is logging for developer mode?
     */
    public function isLoggingDeveloper()
    {
        return $this->appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER;
    }

    /**
     * Gets customer's current currency
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @param string $scope
     * @param null $scopeCode
     *
     * @return mixed
     */
    public function getPaymentRegion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/payment_region',
            $scope,
            $scopeCode
        );
    }

    /**
     * @return string
     */
    public function getStoreName($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/storename',
            $scope,
            $scopeCode
        );
    }

    /**
     * Checks to see if store's selected region is a multicurrency region.
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     * @return bool
     */
    public function isMulticurrencyRegion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null, $store = null)
    {
        $mcRegions = $this->scopeConfig->getValue(
            'multicurrency/regions',
            $scope,
            $store
        );

        if ($mcRegions) {
            $allowedRegions = explode(',', $mcRegions);

            if (in_array($this->getPaymentRegion(), $allowedRegions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check to see if multicurrency is enabled and if it's available for given endpoint/region
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return bool
     */
    public function multiCurrencyEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null, $store = null)
    {
        $enabled = $this->scopeConfig->getValue(
            'payment/amazon_payment/multicurrency',
            $scope,
            $scopeCode
        );

        if ($enabled) {
            return $this->isMulticurrencyRegion($scope, $scopeCode, $store);
        }

        return false;
    }

    /**
     * Only certain currency codes are allowed to be used with multi-currency
     *
     * @param null $store
     * @return bool
     */
    public function useMultiCurrency($store = null)
    {
        if ($this->multiCurrencyEnabled()) {
            // get allowed presentment currencies from config.xml
            $currencies = $this->scopeConfig->getValue(
                'multicurrency/currencies',
                ScopeInterface::SCOPE_STORE,
                $store
            );

            if ($currencies) {
                $allowedCurrencies = explode(',', $currencies);

                if (in_array($this->getCurrentCurrencyCode(), $allowedCurrencies)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @return boolean
     */
    public function isBillingAddressEditable($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return in_array($this->getPaymentRegion($scope, $scopeCode), [
            'de',
            'uk',
        ]);
    }

    /**
     * Return Private Key
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return string
     */
    public function getPrivateKey($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/private_key',
            $scope,
            $scopeCode
        );
    }

    /**
     * Return Public Key
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return string
     */
    public function getPublicKey($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/public_key',
            $scope,
            $scopeCode
        );
    }

    /**
     * Return Public Key ID
     *
     * @param string $scope
     * @param null $scopeCode
     * @param null $store
     *
     * @return string
     */
    public function getPublicKeyId($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/public_key_id',
            $scope,
            $scopeCode
        );
    }

    /**
     * @return string
     */
    public function getMerchantId($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/merchant_id',
            $scope,
            $scopeCode
        );
    }

    /**
     * @return string
     */
    public function getClientId($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/store_id',
            $scope,
            $scopeCode
        );
    }

    /**
     * @return string
     */
    public function getPaymentAction($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/payment_action',
            $scope,
            $scopeCode
        );
    }

    /**
     * @return bool
     */
    public function canHandlePendingAuthorization($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/authorization_mode',
            $scope,
            $scopeCode
        ) == 'synchronous_possible';
    }

    /**
     * @return string
     */
    public function getCheckoutReviewUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue('payment/amazon_payment_v2/checkout_review_url', $scope, $scopeCode);
        if (empty($result)) {
            $result = $this->storeManager->getStore()->getUrl('checkout', ['_forced_secure' => true]);
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getCheckoutResultUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue('payment/amazon_payment_v2/checkout_result_url', $scope, $scopeCode);
        if (empty($result)) {
            $result = $this->storeManager->getStore()->getUrl('amazon_payv2/checkout/completeSession', ['_forced_secure' => true]);
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isSandboxEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (bool)$this->scopeConfig->getValue(
            'payment/amazon_payment/sandbox',
            $scope,
            $scopeCode
        );
    }

    /**
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     */
    public function isPayButtonAvailableInMinicart($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag('payment/amazon_payment/minicart_button_is_visible', $scope, $scopeCode);
    }

   /**
    * @param string $scope
    * @param null|string $scopeCode
    *
    * @return bool
    */
    public function isPayButtonAvailableOnProductPage($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag('payment/amazon_payment/pwa_pp_button_is_visible', $scope, $scopeCode);
    }

    /**
     * @param string $scope
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isPayButtonAvailableAsPaymentMethod($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag('payment/amazonlogin/active', $scope, $scopeCode);
    }

    /**
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     */
    public function isAlexaEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/alexa_active',
            $scope,
            $scopeCode
        );
    }

    /**
     * @return string
     */
    public function getPlatformId()
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/platform_id');
    }
}
