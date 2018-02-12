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

namespace Amazon\Payment\Helper\Shortcut;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
class Validator implements ValidatorInterface
{
    /**
     * @var \Amazon\Payment\Model\Config
     */
    private $_amazonConfigFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_registry;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    private $_productTypeConfig;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $_paymentData;

    /**
     * @var \Amazon\Core\Helper\CategoryExclusion
     */
    private $_categoryExclusionHelper;

    /**
     * @param \Amazon\Payment\Model\Config $amazonConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Amazon\Core\Helper\CategoryExclusion $categoryExclusionHelper
     */
    public function __construct(
        \Amazon\Payment\Model\Config $amazonConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Payment\Helper\Data $paymentData,
        \Amazon\Core\Helper\CategoryExclusion $categoryExclusionHelper
    ) {
        $this->_amazonConfig = $amazonConfig;
        $this->_registry = $registry;
        $this->_productTypeConfig = $productTypeConfig;
        $this->_paymentData = $paymentData;
        $this->_categoryExclusionHelper = $categoryExclusionHelper;
    }

    /**
     * Validates shortcut
     *
     * @param string $code
     * @param bool $isInCatalog
     * @return bool
     */
    public function validate($code, $isInCatalog)
    {
        return $this->isContextAvailable($code, $isInCatalog)
            && $this->isPriceOrSetAvailable($isInCatalog)
            && $this->isMethodAvailable($code);
    }

    /**
     * Checks visibility of context (cart or product page)
     *
     * @param string $paymentCode Payment method code
     * @param bool $isInCatalog
     * @return bool
     */
    public function isContextAvailable($paymentCode, $isInCatalog)
    {
        /** @var \Magento\Paypal\Model\Config $config */
        $config = $this->_amazonConfigFactory->create();
        $config->setMethod($paymentCode);

        // check visibility on product page
        if ($isInCatalog && $config->getValue('pwa_pp_button_is_visible')) {
            $currentProduct = $this->_registry->registry('current_product');
            if ($currentProduct !== null) {
                if ($this->_categoryExclusionHelper->productHasExcludedCategory($currentProduct)) {
                    return false;
                }
            } else {
                if ($this->_categoryExclusionHelper->isQuoteDirty()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Check is product available depending on final price or type set(configurable)
     *
     * @param bool $isInCatalog
     * @return bool
     */
    public function isPriceOrSetAvailable($isInCatalog)
    {
        if ($isInCatalog) {
            // Show PayPal shortcut on a product view page only if product has nonzero price
            /** @var $currentProduct \Magento\Catalog\Model\Product */
            $currentProduct = $this->_registry->registry('current_product');
            if ($currentProduct !== null) {
                $productPrice = (double)$currentProduct->getFinalPrice();
                $typeInstance = $currentProduct->getTypeInstance();
                if (empty($productPrice)
                    && !$this->_productTypeConfig->isProductSet($currentProduct->getTypeId())
                    && !$typeInstance->canConfigure($currentProduct)
                ) {
                    return  false;
                }
            }
        }
        return true;
    }

    /**
     * Checks payment method and quote availability
     *
     * @param string $paymentCode
     * @return bool
     */
    public function isMethodAvailable($paymentCode)
    {
        // check payment method availability
        /** @var \Magento\Payment\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $this->_paymentData->getMethodInstance($paymentCode);
        if (!$methodInstance->isAvailable()) {
            return false;
        }
        return true;
    }
}
