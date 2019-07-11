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

namespace Amazon\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\StatusFactory;
use Amazon\Core\Model\AmazonConfig;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Data extends AbstractHelper
{

    const AMAZON_ACTIVE = 'payment/amazon_payment/active';

    /**
     * @var Config
     */
    private $config;

    /**
     * Data constructor.
     *
     * Because most of these methods have been moved to Amazon\Core\Model\AmazonConfig,
     * there are several unused dependencies here which are not
     * assigned in the constructor.
     * They have been left in the constructor signature to avoid changing the API.
     *
     * @param ModuleListInterface $moduleList
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     * @param ClientIp $clientIpHelper
     * @param StatusFactory $moduleStatusFactory
     * @param AmazonConfig $config
     */
    public function __construct(
        ModuleListInterface $moduleList = null,
        Context $context,
        EncryptorInterface $encryptor = null,
        StoreManagerInterface $storeManager = null,
        ClientIp $clientIpHelper = null,
        StatusFactory $moduleStatusFactory = null,
        AmazonConfig $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getMerchantId($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getMerchantId($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getAccessKey($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getAccessKey($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getSecretKey($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getSecretKey($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getClientId($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getClientId($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getClientSecret($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getClientSecret($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated - use \Amazon\Core\Model\AmazonConfig::getPaymentRegion() instead
     */
    public function getPaymentRegion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getPaymentRegion($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getRegion($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getRegion($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getCurrencyCode($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getCurrencyCode($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getWidgetUrl($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getWidgetUrl($scope, $scopeCode);
    }

    /**
     * Retrieves region path from config.xml settings
     *
     * @param $key
     * @param null $store
     * @return mixed
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getWidgetPath($key, $store = null)
    {
        return $this->config->getWidgetPath($key, $store);
    }

    /**
     * @param string $scope
     * @param null|string $scopeCode
     *
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getLoginScope($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getLoginScope($scope, $scopeCode);
    }

    /**
     * @param string $scope
     *
     * @return boolean
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isEuPaymentRegion($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->config->isEuPaymentRegion($scope);
    }

    /*
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isSandboxEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isSandboxEnabled($scope, $scopeCode);
    }

    /*
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isPwaEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isPwaEnabled($scope, $scopeCode);
    }

    /*
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isLwaEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isLwaEnabled($scope, $scopeCode);
    }

    /*
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isEnabled($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getPaymentAction($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getPaymentAction($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getAuthorizationMode($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getAuthorizationMode($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getUpdateMechanism($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getUpdateMechanism($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getButtonDisplayLanguage($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getButtonDisplayLanguage($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getButtonType($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getButtonType($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getButtonTypePwa($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getButtonTypePwa($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getButtonTypeLwa($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getButtonTypeLwa($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getButtonColor($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getButtonColor($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getButtonSize($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getButtonSize($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getEmailStoreName($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getEmailStoreName($scope, $scopeCode);
    }

    /*
     * @return string
     */
    public function getAdditionalAccessScope($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getAdditionalAccessScope($scope, $scopeCode);
    }

    /*
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isLoggingEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isLoggingEnabled($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getStoreName($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getStoreName($scope, $scopeCode);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getStoreFrontName($storeId)
    {
        return $this->config->getStoreFrontName($storeId);
    }

    /*
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getRedirectUrl()
    {
        return $this->config->getRedirectUrl();
    }

    /**
     * @param string|null $context
     *
     * @return array
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getSandboxSimulationStrings($context = null)
    {
        return $this->config->getSandboxSimulationStrings($context);
    }

    /**
     * @return array
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getSandboxSimulationOptions()
    {
        return $this->config->getSandboxSimulationOptions();
    }

    /**
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isPaymentButtonEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isPaymentButtonEnabled($scope, $scopeCode);
    }

    /**
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isLoginButtonEnabled()
    {
        return $this->config->isLoginButtonEnabled();
    }

    /**
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isCurrentCurrencySupportedByAmazon()
    {
        return $this->config->isCurrentCurrencySupportedByAmazon();
    }

    /**
     * @param string $paymentRegion E.g. "uk", "us", "de", "jp".
     *
     * @return mixed
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getAmazonAccountUrlByPaymentRegion($paymentRegion)
    {
        return $this->config->getAmazonAccountUrlByPaymentRegion($paymentRegion);
    }

    /**
     * Retrieves region path from config.xml settings
     *
     * @param $key
     * @param null $store
     * @return mixed
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getPaymentRegionUrl($key, $store = null)
    {
        return $this->config->getPaymentRegionUrl($key, $store);
    }

    /**
     * Retrieves client path from config.xml settings
     *
     * @param $key
     * @param null $store
     * @return mixed
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getClientPath($key, $store = null)
    {
        return $this->config->getClientPath($key, $store);
    }

    /**
     * @param string $scope
     * @param null|string $scopeCode
     *
     * @return array
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getBlackListedTerms($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getBlackListedTerms($scope, $scopeCode);
    }

    /**
     * @param string $scope
     * @param null|string $scopeCode
     *
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isBlacklistedTermValidationEnabled($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isBlacklistedTermValidationEnabled($scope, $scopeCode);
    }

    /**
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getOAuthRedirectUrl()
    {
        return $this->config->getOAuthRedirectUrl();
    }

    /**
     * @param string $scope
     * @param null|string $scopeCode
     *
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isPwaButtonVisibleOnProductPage($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isPwaButtonVisibleOnProductPage($scope, $scopeCode);
    }

    /**
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function isPayButtonAvailableInMinicart($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->isPayButtonAvailableInMinicart($scope, $scopeCode);
    }

    /**
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function allowAmLoginLoading($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->allowAmLoginLoading($scope, $scopeCode);
    }

    /**
     * @param string $scope
     * @param null|string $scopeCode
     *
     * @return string
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getCredentialsJson($scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->config->getCredentialsJson($scope, $scopeCode);
    }

    /**
     * @return array
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getAmazonCredentialsFields()
    {
        return $this->config->getAmazonCredentialsFields();
    }

    /**
     * @return array
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getAmazonCredentialsEncryptedFields()
    {
        return $this->config->getAmazonCredentialsEncryptedFields();
    }

    /**
     * @return null
     *
     * @deprecated Use \Amazon\Core\Model\AmazonConfig instead
     */
    public function getVersion()
    {
        return $this->config->getVersion();
    }
}
