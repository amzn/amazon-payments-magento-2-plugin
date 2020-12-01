<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Amazon\Core\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AmazonConfig
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Config constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Gets customer's current currency
     *
     * @param null $store
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCurrentCurrencyCode($store = null)
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
     * Checks to see if store's selected region is a multicurrency region.
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     */
    public function isMulticurrencyRegion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $mcRegions = $this->scopeConfig->getValue(
            'multicurrency/regions',
            $scope,
            $scopeCode
        );

        if ($mcRegions) {
            $allowedRegions = explode(',', $mcRegions);

            if (in_array($this->getPaymentRegion($scope, $scopeCode), $allowedRegions)) {
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
     *
     * @return bool
     */
    public function multiCurrencyEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $enabled = $this->scopeConfig->getValue(
            'payment/amazon_payment/multicurrency',
            $scope,
            $scopeCode
        );

        if ($enabled) {
            return $this->isMulticurrencyRegion($scope, $scopeCode);
        }

        return false;
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @return array
     */
    public function getValidCurrencies($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return explode(',', $this->scopeConfig->getValue('multicurrency/currencies', $scope, $scopeCode));
    }

    /**
     * @param string $currencyCode
     * @param string $scope
     * @param string $scopeCode
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
     * Only certain currency codes are allowed to be used with multi-currency
     *
     * @param null $store
     * @return bool
     */
    public function useMultiCurrency($store = null)
    {
        return $this->canUseCurrency($this->getCurrentCurrencyCode(), ScopeInterface::SCOPE_STORE, $store);
    }

    /*
     * @return string
    */
    public function getPresentmentCurrency()
    {
        return $this->getCurrentCurrencyCode();
    }

    /**
     * Retrieves the base currency of the store.
     *
     * @param null $store
     * @return mixed
     */
    public function getBaseCurrencyCode($store = null)
    {
        return $this->scopeConfig->getValue(
            'currency/options/base',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is AmazonWebapiException code a soft decline error?
     *
     * @param $errorCode
     * @return bool
     */
    public function isSoftDecline($errorCode)
    {
        return $errorCode == $this->scopeConfig->getValue('payment/amazon_payment/soft_decline_code');
    }
}
