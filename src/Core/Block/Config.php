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
use Amazon\Core\Helper\Data;
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
     * @var Data
     */
    private $coreHelper;

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
     * @param Data $coreHelper
     * @param AmazonConfig $config
     * @param Url $url
     * @param CategoryExclusion $categoryExclusionHelper
     */
    public function __construct(
        Context $context,
        Data $coreHelper,
        AmazonConfig $config,
        Url $url,
        CategoryExclusion $categoryExclusionHelper
    ) {
        parent::__construct($context);
        $this->coreHelper = $coreHelper;
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
            'widgetUrl'                => $this->coreHelper->getWidgetUrl(),
            'merchantId'               => $this->coreHelper->getMerchantId(),
            'clientId'                 => $this->coreHelper->getClientId(),
            'isPwaEnabled'             => $this->coreHelper->isPaymentButtonEnabled(),
            'isLwaEnabled'             => $this->coreHelper->isLoginButtonEnabled(),
            'isSandboxEnabled'         => $this->coreHelper->isSandboxEnabled(),
            'chargeOnOrder'            => $this->sanitizePaymentAction(),
            'authorizationMode'        => $this->coreHelper->getAuthorizationMode(),
            'displayLanguage'          => $this->coreHelper->getButtonDisplayLanguage(),
            'buttonTypePwa'            => $this->coreHelper->getButtonTypePwa(),
            'buttonTypeLwa'            => $this->coreHelper->getButtonTypeLwa(),
            'buttonColor'              => $this->coreHelper->getButtonColor(),
            'buttonSize'               => $this->coreHelper->getButtonSize(),
            'redirectUrl'              => $this->coreHelper->getRedirectUrl(),
            'loginPostUrl'             => $this->url->getLoginPostUrl(),
            'customerLoginPageUrl'     => $this->url->getLoginUrl(),
            'sandboxSimulationOptions' => [],
            'loginScope'               => $this->coreHelper->getLoginScope(),
            'allowAmLoginLoading'      => $this->coreHelper->allowAmLoginLoading(),
            'isEuPaymentRegion'        => $this->coreHelper->isEuPaymentRegion(),
            'presentmentCurrency'      => $this->config->getPresentmentCurrency(),
            'oAuthHashRedirectUrl'     => $this->coreHelper->getOAuthRedirectUrl(),
            'isQuoteDirty'             => $this->categoryExclusionHelper->isQuoteDirty(),
            'region'                   => $this->coreHelper->getRegion(),
            'useMultiCurrency'         => $this->config->useMultiCurrency(),
            'scaRegions'               => $this->coreHelper->getScaRegions()
        ];

        if ($this->coreHelper->isSandboxEnabled()) {
            $config['sandboxSimulationOptions'] = $this->transformSandboxSimulationOptions();
        }

        if ($orderReferenceId = $this->getRequest()->getParam('orderReferenceId')) {
            $config['orderReferenceId'] = preg_replace('/[^A-Z0-9-]/', '', $orderReferenceId);
        }

        return $config;
    }

    /**
     * @return bool
     */
    public function isBadgeEnabled()
    {
        return ($this->coreHelper->isPwaEnabled());
    }

    /**
     * @return bool
     */
    public function isExtensionEnabled()
    {
        return ($this->coreHelper->isPwaEnabled() || $this->coreHelper->isLwaEnabled());
    }

    /**
     * @return bool
     */
    public function sanitizePaymentAction()
    {
        return ($this->coreHelper->getPaymentAction() === 'authorize_capture');
    }

    /**
     * @return array
     */
    public function transformSandboxSimulationOptions()
    {
        $sandboxSimulationOptions = $this->coreHelper->getSandboxSimulationOptions();
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
