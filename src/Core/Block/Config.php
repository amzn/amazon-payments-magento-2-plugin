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
namespace Amazon\Core\Block;

use Amazon\Core\Helper\CategoryExclusion;
use Amazon\Core\Model\AmazonConfig;
use Magento\Customer\Model\Url;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Config
 *
 * @api
 *
 * Provides a block that displays links to available custom error logs in Amazon Pay admin/config section.
 */
class Config extends Template
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var CategoryExclusion
     */
    private $categoryExclusionHelper;

    /**
     * @var AmazonConfig
     */
    private $config;

    /**
     * Config constructor.
     * @param Context $context
     * @param AmazonConfig $config
     * @param Url $url
     * @param CategoryExclusion $categoryExclusionHelper
     */
    public function __construct(
        Context $context,
        AmazonConfig $config,
        Url $url,
        CategoryExclusion $categoryExclusionHelper
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->url = $url;
        $this->categoryExclusionHelper = $categoryExclusionHelper;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        $config = [
            'widgetUrl'                => $this->config->getWidgetUrl(),
            'merchantId'               => $this->config->getMerchantId(),
            'clientId'                 => $this->config->getClientId(),
            'isPwaEnabled'             => $this->config->isPaymentButtonEnabled(),
            'isLwaEnabled'             => $this->config->isLoginButtonEnabled(),
            'isSandboxEnabled'         => $this->config->isSandboxEnabled(),
            'chargeOnOrder'            => $this->sanitizePaymentAction(),
            'authorizationMode'        => $this->config->getAuthorizationMode(),
            'displayLanguage'          => $this->config->getButtonDisplayLanguage(),
            'buttonTypePwa'            => $this->config->getButtonTypePwa(),
            'buttonTypeLwa'            => $this->config->getButtonTypeLwa(),
            'buttonColor'              => $this->config->getButtonColor(),
            'buttonSize'               => $this->config->getButtonSize(),
            'redirectUrl'              => $this->config->getRedirectUrl(),
            'loginPostUrl'             => $this->url->getLoginPostUrl(),
            'customerLoginPageUrl'     => $this->url->getLoginUrl(),
            'sandboxSimulationOptions' => [],
            'loginScope'               => $this->config->getLoginScope(),
            'allowAmLoginLoading'      => $this->config->allowAmLoginLoading(),
            'isEuPaymentRegion'        => $this->config->isEuPaymentRegion(),
            'presentmentCurrency'      => $this->config->getPresentmentCurrency(),
            'oAuthHashRedirectUrl'     => $this->config->getOAuthRedirectUrl(),
            'isQuoteDirty'             => $this->categoryExclusionHelper->isQuoteDirty(),
            'region'                   => $this->config->getRegion(),
            'useMultiCurrency'         => $this->config->useMultiCurrency()
        ];

        if ($this->config->isSandboxEnabled()) {
            $config['sandboxSimulationOptions'] = $this->transformSandboxSimulationOptions();
        }

        return $config;
    }

    /**
     * @return bool
     */
    public function isBadgeEnabled()
    {
        return ($this->config->isPwaEnabled());
    }

    /**
     * @return bool
     */
    public function isExtensionEnabled()
    {
        return ($this->config->isPwaEnabled() || $this->config->isLwaEnabled());
    }

    /**
     * @return bool
     */
    public function sanitizePaymentAction()
    {
        return ($this->config->getPaymentAction() === 'authorize_capture');
    }

    /**
     * @return array
     */
    public function transformSandboxSimulationOptions()
    {
        $sandboxSimulationOptions = $this->config->getSandboxSimulationOptions();
        $output = [];

        foreach ($sandboxSimulationOptions as $key => $value) {
            $output[] = [
                'labelText'       => $value,
                'simulationValue' => $key,
            ];
        }

        return $output;
    }
}
