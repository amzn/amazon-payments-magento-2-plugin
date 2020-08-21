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

class ProductPagePaymentLink extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\PayV2\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\PayV2\Helper\Data $amazonHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->amazonConfig = $amazonConfig;
        $this->amazonHelper = $amazonHelper;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->amazonConfig->isEnabled() || $this->amazonHelper->isProductRestricted($this->_getProduct()) || !$this->amazonConfig->isPayButtonAvailableOnProductPage()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function _getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * @return bool
     */
    public function isPayOnly()
    {
        return $this->_getProduct()->isVirtual();
    }
}
