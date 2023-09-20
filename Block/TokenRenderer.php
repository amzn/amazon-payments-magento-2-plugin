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

use Magento\Vault\Block\AbstractTokenRenderer;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Amazon\Pay\Gateway\Config\Config;
use Amazon\Pay\Model\AmazonConfig;
use Amazon\Pay\Helper\SubscriptionHelper;
use Magento\Framework\View\Element\Template\Context;

class TokenRenderer extends AbstractTokenRenderer
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var SubscriptionHelper
     */
    private $helper;

    /**
     * @param Context $context
     * @param AmazonConfig $amazonConfig
     * @param SubscriptionHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        AmazonConfig $amazonConfig,
        SubscriptionHelper $helper,
        array $data = []
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === Config::CODE;
    }

    /**
     * Get Amazon icon url
     *
     * @return string
     */
    public function getIconUrl()
    {
        return $this->amazonConfig->getAmazonIcon()['url'];
    }

    /**
     * Get Amazon icon height
     *
     * @return int
     */
    public function getIconHeight()
    {
        return $this->amazonConfig->getAmazonIcon()['height'];
    }

    /**
     * Get Amazon icon width
     *
     * @return int
     */
    public function getIconWidth()
    {
        return $this->amazonConfig->getAmazonIcon()['width'];
    }

    /**
     * Get payment descriptor
     *
     * @return string
     */
    public function getPaymentDescriptor()
    {
        return $this->helper->getTokenPaymentDescriptor($this->getToken());
    }
}
