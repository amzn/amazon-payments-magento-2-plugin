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

}
