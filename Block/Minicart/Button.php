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
namespace Amazon\Pay\Block\Minicart;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;

class Button extends Template implements ShortcutInterface
{
    public const ALIAS_ELEMENT_INDEX = 'alias';

    public const CART_BUTTON_ELEMENT_INDEX = 'add_to_cart_selector';

    /**
     * @var bool
     */
    private $isMiniCart = false;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Pay\Helper\Data
     */
    private $amazonHelper;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * Return true if module is enabled and configs allow it
     *
     * @return bool
     */
    protected function shouldRender()
    {
        if ($this->getIsCart() && $this->amazonConfig->isEnabled()) {
            return true;
        }

        return $this->amazonConfig->isEnabled()
            && $this->amazonConfig->isPayButtonAvailableInMinicart()
            && $this->isMiniCart;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return true if on customer cart page
     *
     * @return bool
     */
    protected function _isOnCartPage()
    {
        return $this->request->getFullActionName() == 'checkout_cart_index';
    }

    /**
     * Returns add to cart selector
     *
     * @return string
     */
    public function getAddToCartSelector()
    {
        return $this->getData(self::CART_BUTTON_ELEMENT_INDEX);
    }

    /**
     * Returns image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->config->getExpressCheckoutInContextImageUrl(
            $this->localeResolver->getLocale()
        );
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * Set information if button renders in the mini cart
     *
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }
}
