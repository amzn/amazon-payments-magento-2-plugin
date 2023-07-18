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

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductPagePaymentLink extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Pay\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * ProductPagePaymentLink constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Pay\Helper\Data $amazonHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Amazon\Pay\Helper\Data $amazonHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->amazonConfig = $amazonConfig;
        $this->amazonHelper = $amazonHelper;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->amazonConfig->isEnabled() ||
            $this->amazonHelper->isProductRestricted($this->_getProduct()) ||
            !$this->amazonConfig->isPayButtonAvailableOnProductPage()
        ) {
            return '';
        }

        // check for product stock and/or saleability
        $product = $this->_getProduct();
        // configurable products
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            if (!$product->isSaleable()) {
                return '';
            }
        } else {
            // other product types
            if ($product->isInStock() == 0 || !$product->isSaleable()) {
                return '';
            }
        }

        return parent::_toHtml();
    }

    /**
     * Get product from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * Return true if product is digital
     *
     * @return bool
     */
    public function isPayOnly()
    {
        return $this->_getProduct()->isVirtual();
    }
}
