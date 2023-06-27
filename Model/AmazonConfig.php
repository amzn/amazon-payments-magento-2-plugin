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

namespace Amazon\Pay\Model;

use Magento\Store\Model\ScopeInterface;

class AmazonConfig
{
    public const LANG_DE = 'de_DE';
    public const LANG_FR = 'fr_FR';
    public const LANG_ES = 'es_ES';
    public const LANG_IT = 'it_IT';
    public const LANG_JA = 'ja_JP';
    public const LANG_UK = 'en_GB';
    public const LANG_US = 'en_US';
    public const EUROPEAN_LOCALES = [
        self::LANG_UK,
        self::LANG_DE,
        self::LANG_FR,
        self::LANG_IT,
        self::LANG_ES,
    ];
    public const EUROPEAN_REGIONS = [
        'de',
        'uk',
    ];

    /**
     * @var array
     */
    private $icon = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\AllowedCountries
     */
    private $countriesAllowed;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $countryConfig;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * AmazonConfig constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\AllowedCountries $countriesAllowed
     * @param \Magento\Directory\Model\Config\Source\Country $countryConfig
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Payment\Model\CcConfig $ccConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\AllowedCountries $countriesAllowed,
        \Magento\Directory\Model\Config\Source\Country $countryConfig,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Payment\Model\CcConfig $ccConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->countriesAllowed = $countriesAllowed;
        $this->countryConfig = $countryConfig;
        $this->localeResolver = $localeResolver;
        $this->remoteAddress = $remoteAddress;
        $this->serializer = $serializer;
        $this->ccConfig = $ccConfig;
    }

    /**
     * True if AP is enabled and available for customer/store
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return bool
     */
    public function isEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        if (!$this->clientHasAllowedIp()) {
            return false;
        }

        if (!$this->isCurrentCurrencySupportedByAmazon($scope, $scopeCode)) {
            return false;
        }

        return $this->isActive($scope, $scopeCode);
    }

    /**
     * True if module is enabled from admin config
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function isActive($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            'payment/amazon_payment_v2/active',
            $scope,
            $scopeCode
        );
    }

    /**
     * Get Amazon payment region
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getRegion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->getPaymentRegion($scope, $scopeCode);
    }

    /**
     * Get configured button color
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getButtonColor($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/button_color', $scope, $scopeCode);
    }

    /**
     * Get configured display language
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getLanguage($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $paymentRegion = $this->getRegion($scope, $scopeCode);

        // check if button language is set and it matches allowed region and options
        $lang = $this->scopeConfig->getValue(
            'payment/amazon_pay/button_display_language',
            $scope,
            $scopeCode
        );

        if ($lang) {
            if (in_array($lang, self::EUROPEAN_LOCALES)
                && in_array($paymentRegion, self::EUROPEAN_REGIONS)) {
                return $lang;
            }
        }

        $localeParts = explode('_', $this->localeResolver->getLocale());
        $lang = $localeParts[0];
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
     * Get Amazon icon
     *
     * @return array
     */
    public function getAmazonIcon(): array
    {
        if (empty($this->icon)) {
            $asset = $this->ccConfig->createAsset('Amazon_Pay::images/logo/Black-L.png');
            list($width, $height) = getimagesizefromstring($asset->getSourceFile());
            $this->icon = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height
            ];
        }

        return $this->icon;
    }

    /**
     * True if current currency can be used with Amazon Pay
     *
     * This is true if:
     * 1. Currency aligns with Amazon payment region, or
     * 2. Multicurrency is enabled, available for the payment region, and the current currency is
     * one of the allowed currencies
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return bool
     */
    public function isCurrentCurrencySupportedByAmazon($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $regionCurrency = $this->getCurrentCurrencyCode();
        $currentCurrency = $this->getCurrencyCode($scope, $scopeCode);
        return ($currentCurrency === $regionCurrency) || $this->canUseCurrency($regionCurrency);
    }

    /**
     * Get currency by payment region
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * True if multicurrency is enabled and the currency code is valid for the store
     *
     * @param string $currencyCode
     * @param string $scope
     * @param int|string $scopeCode
     * @return boolean
     */
    public function canUseCurrency($currencyCode, $scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = false;
        if ($this->multiCurrencyEnabled($scope, $scopeCode)) {
            $result = in_array($currencyCode, $this->getValidCurrencies($scope, $scopeCode));
        }
        return $result;
    }

    /**
     * Get list of valid currencied for store
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return array
     */
    public function getValidCurrencies($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return explode(',', $this->scopeConfig->getValue('multicurrency/currencies', $scope, $scopeCode));
    }

    /**
     * Is debug logging enabled?
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Get configured payment region
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Get configured store name
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @param int|string $store
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
     * @param int|string $scopeCode
     * @param int|string $store
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
     * @param int|string $store
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
     * Return Private Key
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Return Private Key Selected method (text or pem)
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getPrivateKeySelected($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/private_key_selected',
            $scope,
            $scopeCode
        );
    }

    /**
     * Return Public Key
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * @param int|string $scopeCode
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
     * Get configured merchant ID
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Get configured client ID
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Get configured payment action (charge on shipment/charge on order)
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Get configured authorization mode (automatic/immediate)
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return mixed
     */
    public function getAuthorizationMode($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            'payment/amazon_payment/authorization_mode',
            $scope,
            $scopeCode
        );
    }

    /**
     * True if configured auth mode is automatic
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Get configured checkout review return URL
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getCheckoutReviewReturnUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $route = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/checkout_review_return_url',
            $scope,
            $scopeCode
        );
        return $this->storeManager->getStore()->getUrl(
            $route,
            ['_forced_secure' => true]
        );
    }

    /**
     * Get configured checkout review URL
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getCheckoutReviewUrlPath($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/checkout_review_url',
            $scope,
            $scopeCode
        );
        if (empty($result)) {
            $result = 'checkout';
        }
        return $result;
    }

    /**
     * Get configured checkout result return URL
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getCheckoutResultReturnUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $route = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/checkout_result_return_url',
            $scope,
            $scopeCode
        );
        return $this->storeManager->getStore()->getUrl(
            $route,
            ['_forced_secure' => true]
        );
    }

    /**
     * Get configured checkout result URL
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getCheckoutResultUrlPath($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/checkout_result_url',
            $scope,
            $scopeCode
        );
        if (empty($result)) {
            $result = 'checkout/onepage/success';
        }
        return $result;
    }

    /**
     * Get configured sign in result URL
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getSignInResultUrlPath($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/sign_in_result_url',
            $scope,
            $scopeCode
        );
        if (empty($result)) {
            $result = 'amazon_pay/login/authorize/';
        }
        return $result;
    }

    /**
     * Get result URL for APB flow
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string
     */
    public function getPayNowResultUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->getCheckoutResultReturnUrl($scope, $scopeCode);
    }

    /**
     * Get configured cancel URL if set
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string|null
     */
    public function getCheckoutCancelUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/checkout_cancel_url',
            $scope,
            $scopeCode
        );

        return $result;
    }

    /**
     * Get sign in cancel URL if set
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return string|null
     */
    public function getSignInCancelUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/signin_cancel_url',
            $scope,
            $scopeCode
        );

        return $result;
    }

    /**
     * Get list of IP addresses for whom Amazon Pay can be used
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return array
     */
    public function getAllowedIps($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $allowedIpsString = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/allowed_ips',
            $scope,
            $scopeCode
        );
        return empty($allowedIpsString) ? [] : explode(',', $allowedIpsString);
    }

    /**
     * True if client IP is in list of allowed IPs
     *
     * Also returns true if no IPs have been declared as allowed, indicating that
     * all customers should be able to use Amazon Pay.
     *
     * @return bool
     */
    public function clientHasAllowedIp()
    {
        // e.g. X-Forwarded-For can have a comma-separated list of IPs
        $clientIp = explode(',', $this->remoteAddress->getRemoteAddress())[0];
        $allowedIps = $this->getAllowedIps();
        return empty($allowedIps) ? true : in_array($clientIp, $allowedIps);
    }

    /**
     * True if sandbox mode is enabled
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * True if button is configured to display in minicart
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return bool
     */
    public function isPayButtonAvailableInMinicart($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            'payment/amazon_payment/minicart_button_is_visible',
            $scope,
            $scopeCode
        );
    }

   /**
    * True if button is configured to display on PDP
    *
    * @param string $scope
    * @param null|string $scopeCode
    * @return bool
    */
    public function isPayButtonAvailableOnProductPage($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            'payment/amazon_payment/pwa_pp_button_is_visible',
            $scope,
            $scopeCode
        );
    }

    /**
     * True if button is configured to display at last step of checkout (Pay Now)
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return bool
     */
    public function isPayButtonAvailableAsPaymentMethod($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            'payment/amazonlogin/active',
            $scope,
            $scopeCode
        );
    }

    /**
     * Get list of categories for which Amazon Pay cannot be used
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return array
     */
    public function getRestrictedCategoryIds($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $value = $this->scopeConfig->getValue('payment/amazon_payment_v2/restrict_categories', $scope, $scopeCode);
        return !empty($value) ? explode(',', $value) : [];
    }

    /**
     * True if Alexa delivery notifications are enabled
     *
     * @param string $scope
     * @param int|string $scopeCode
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
     * Get list of carrier codes => Amazon carrier codes
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return array
     */
    public function getCarriersMapping($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $mapping = [];
        $configValues = $this->scopeConfig->getValue(
            'payment/amazon_payment_v2/alexa_carrier_codes',
            $scope,
            $scopeCode
        );

        if ($configValues) {
            $configValues = $this->serializer->unserialize($configValues);
            if (count($configValues) > 0) {
                foreach (array_values($configValues) as $row) {
                    $mapping[$row['carrier']] = $row['amazon_carrier'];
                }
            }
        }

        return $mapping;
    }

    /**
     * Get list of allowable addresses
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return array
     */
    public function getDeliverySpecifications($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = [];
        $allCountries = array_column($this->countryConfig->toOptionArray(true), 'value');
        $allowedCountries = $this->countriesAllowed->getAllowedCountries($scope, $scopeCode);
        $allCountriesCount = count($allCountries);
        $allowedCountriesCount = count($allowedCountries);
        if ($allowedCountriesCount < $allCountriesCount) {
            if ($allowedCountriesCount < $allCountriesCount / 2) {
                $type = 'Allowed';
                $countries = $allowedCountries;
            } else {
                $type = 'NotAllowed';
                $countries = array_diff($allCountries, $allowedCountries);
            }
            $restrictions = [];
            foreach ($countries as $country) {
                if (empty(trim($country))) {
                    continue;
                }
                $restrictions[$country] = new \stdClass();
            }
            $result = [
                'addressRestrictions' => [
                    'type' => $type,
                    'restrictions' => $restrictions,
                ],
            ];
        }
        $specialRestrictions = [];
        if ($this->scopeConfig->getValue(
            'payment/amazon_payment_v2/shipping_restrict_po_boxes',
            $scope,
            $scopeCode
        )) {
            $specialRestrictions[] = 'RestrictPOBoxes';
        }
        if ($this->scopeConfig->getValue(
            'payment/amazon_payment_v2/shipping_restrict_packstations',
            $scope,
            $scopeCode
        )) {
            $specialRestrictions[] = 'RestrictPackstations';
        }
        if (!empty($specialRestrictions)) {
            $result['specialRestrictions'] = $specialRestrictions;
        }
        return $result;
    }

    /**
     * Get platform (Magento) ID
     *
     * @return string
     */
    public function getPlatformId()
    {
        return $this->scopeConfig->getValue('payment/amazon_payment_v2/platform_id');
    }

    /**
     * True if Amazon Sign In is enabled
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return bool
     */
    public function isLwaEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!$this->clientHasAllowedIp()) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            'payment/amazon_payment_v2/lwa_enabled',
            $scope,
            $scopeCode
        );
    }

    /**
     * True if customer can checkout as guest
     *
     * @param string $scope
     * @param int|string $scopeCode
     * @return bool
     */
    public function isGuestCheckoutEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            'checkout/options/guest_checkout',
            $scope,
            $scopeCode
        );
    }

    /**
     * Check if vault is enabled for scope
     *
     * @param string $scope
     * @param int|null|mixed $scopeCode
     * @return bool
     */
    public function isVaultEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            'payment/amazon_payment_v2_vault/active',
            $scope,
            $scopeCode
        );
    }
}
