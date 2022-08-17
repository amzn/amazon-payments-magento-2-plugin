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
namespace Amazon\Pay\Block;

/**
 * Config
 *
 * @api
 *
 * Provides a block that displays links to available custom error logs in Amazon Pay admin/config section.
 */
class Config extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amazon\Pay\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \ParadoxLabs\Subscriptions\Model\Config
     */
    private $subscriptionConfig;

    /**
     * Config constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amazon\Pay\Helper\Data $amazonHelper
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \ParadoxLabs\Subscriptions\Model\Config $subscriptionConfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amazon\Pay\Helper\Data $amazonHelper,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \ParadoxLabs\Subscriptions\Model\Config $subscriptionConfig
    ) {
        parent::__construct($context);
        $this->amazonHelper = $amazonHelper;
        $this->amazonConfig = $amazonConfig;
        $this->subscriptionConfig = $subscriptionConfig;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'region'                   => $this->amazonConfig->getRegion(),
            'code'                     => \Amazon\Pay\Gateway\Config\Config::CODE,
            'vault_code'               => \Amazon\Pay\Gateway\Config\Config::VAULT_CODE,
            'is_method_available'      => $this->amazonConfig->isPayButtonAvailableAsPaymentMethod(),
            'is_pay_only'              => $this->amazonHelper->isPayOnly(),
            'is_lwa_enabled'            => $this->isLwaEnabled(),
            'is_guest_checkout_enabled' => $this->amazonConfig->isGuestCheckoutEnabled(),
            'has_restricted_products'   => $this->amazonHelper->hasRestrictedProducts(),
            'is_multicurrency_enabled'     => $this->amazonConfig->multiCurrencyEnabled(),
            'subscription_label'        => $this->subscriptionConfig->getSubscriptionLabel()
        ];

        return $config;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->amazonConfig->isEnabled();
    }

    /**
     * @return bool
     */
    public function isLwaEnabled()
    {
        return $this->amazonConfig->isLwaEnabled();
    }
}
