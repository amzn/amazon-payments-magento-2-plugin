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
namespace Amazon\PayV2\Block;

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
     * @var \Amazon\PayV2\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * Config constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amazon\PayV2\Helper\Data $amazonHelper
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amazon\PayV2\Helper\Data $amazonHelper,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig
    ) {
        parent::__construct($context);
        $this->amazonHelper = $amazonHelper;
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        $config = [
            'region'                   => $this->amazonConfig->getRegion(),
            'code'                     => \Amazon\PayV2\Gateway\Config\Config::CODE,
            'is_method_available'      => $this->amazonConfig->isPayButtonAvailableAsPaymentMethod(),
            'is_pay_only'              => $this->amazonHelper->isPayOnly(),
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
