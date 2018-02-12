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
namespace Amazon\Payment\Block\Minicart;

use Magento\Checkout\Model\Session;
use Magento\Payment\Model\MethodInterface;
use Amazon\Payment\Helper\Data;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Magento\Paypal\Block\Express\InContext;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Request\Http;

/**
 * Class Button
 *
 * @api
 */
class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const CART_BUTTON_ELEMENT_INDEX = 'add_to_cart_selector';

    /**
     * @var bool
     */
    private $isMiniCart = false;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Data
     */
    private $mainHelper;

    /**
     * @var MethodInterface
     */
    private $payment;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var AmazonCoreHelper
     */
    private $coreHelper;

    /**
     * @var Http
     */
    private $request;

    /**
     * Button constructor.
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Data $mainHelper
     * @param Session $session
     * @param MethodInterface $payment
     * @param AmazonCoreHelper $coreHelper
     * @param Http $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        Data $mainHelper,
        Session $session,
        MethodInterface $payment,
        AmazonCoreHelper $coreHelper,
        Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->localeResolver = $localeResolver;
        $this->mainHelper = $mainHelper;
        $this->payment = $payment;
        $this->session = $session;
        $this->coreHelper = $coreHelper;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    protected function shouldRender()
    {
        if ($this->getIsCart() && $this->payment->isAvailable($this->session->getQuote())) {
            return true;
        }

        return $this->coreHelper->isPayButtonAvailableInMinicart()
            && $this->payment->isAvailable($this->session->getQuote())
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

    protected function _isOnCartPage()
    {
        return $this->request->getFullActionName() == 'checkout_cart_index';
    }

    /**
     * @return string
     */
    public function getAddToCartSelector()
    {
        return $this->getData(self::CART_BUTTON_ELEMENT_INDEX);
    }

    /**
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
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }
}
