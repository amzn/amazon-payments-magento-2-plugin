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
     * @var \Amazon\Payment\Gateway\Config\Config 
     */
    private $amazonConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    private $productTypeConfig;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentData;

    /**
     * @var \Amazon\Core\Helper\CategoryExclusion
     */
    private $categoryExclusionHelper;

    /**
     * Validator constructor.
     *
     * @param \Amazon\Payment\Gateway\Config\Config               $amazonConfig
     * @param \Magento\Framework\Registry                         $registry
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Payment\Helper\Data                        $paymentData
     * @param \Amazon\Core\Helper\CategoryExclusion               $categoryExclusionHelper
     */
    public function __construct(
        \Amazon\Payment\Gateway\Config\Config $amazonConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Payment\Helper\Data $paymentData,
        \Amazon\Core\Helper\CategoryExclusion $categoryExclusionHelper
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->registry = $registry;
        $this->productTypeConfig = $productTypeConfig;
        $this->paymentData = $paymentData;
        $this->categoryExclusionHelper = $categoryExclusionHelper;
    }

    /**
     * Validates shortcut
     *
     * @param  string $code
     * @param  bool   $isInCatalog
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
     * @param  string $paymentCode Payment method code
     * @param  bool   $isInCatalog
     * @return bool
     */
    public function isContextAvailable($paymentCode, $isInCatalog)
    {
        $this->amazonConfig->setMethodCode($this->amazonConfig::CODE);

        // check visibility on product page
        if ($isInCatalog && $this->amazonConfig->getValue('pwa_pp_button_is_visible')) {
            $currentProduct = $this->registry->registry('current_product');
            if ($currentProduct !== null) {
                if ($this->categoryExclusionHelper->productHasExcludedCategory($currentProduct)) {
                    return false;
                }
            } else {
                if ($this->categoryExclusionHelper->isQuoteDirty()) {
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
     * @param  bool $isInCatalog
     * @return bool
     */
    public function isPriceOrSetAvailable($isInCatalog)
    {
        if ($isInCatalog) {
            // Show PayPal shortcut on a product view page only if product has nonzero price
            /** @var $currentProduct \Magento\Catalog\Model\Product */
            $currentProduct = $this->registry->registry('current_product');
            if ($currentProduct !== null) {
                $productPrice = (double)$currentProduct->getFinalPrice();
                $typeInstance = $currentProduct->getTypeInstance();
                if (empty($productPrice)
                    && !$this->productTypeConfig->isProductSet($currentProduct->getTypeId())
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
     * @param  string $paymentCode
     * @return bool
     */
    public function isMethodAvailable($paymentCode)
    {
        // check payment method availability
        /** @var \Magento\Payment\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $this->paymentData->getMethodInstance($paymentCode);
        if (!$methodInstance->isAvailable()) {
            return false;
        }
        return true;
    }
}
