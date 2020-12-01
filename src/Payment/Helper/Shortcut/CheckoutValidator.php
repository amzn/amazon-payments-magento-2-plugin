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
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class CheckoutValidator implements ValidatorInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $_paymentData;

    /**
     * @var Validator
     */
    private $_shortcutValidator;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Validator                       $shortcutValidator
     * @param \Magento\Payment\Helper\Data    $paymentData
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        Validator $shortcutValidator,
        \Magento\Payment\Helper\Data $paymentData
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_paymentData = $paymentData;
        $this->_shortcutValidator = $shortcutValidator;
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
        return $this->_shortcutValidator->isContextAvailable($code, $isInCatalog)
            && $this->_shortcutValidator->isPriceOrSetAvailable($isInCatalog)
            && $this->isMethodQuoteAvailable($code, $isInCatalog)
            && $this->isQuoteSummaryValid($isInCatalog);
    }

    /**
     * Checks payment method and quote availability
     *
     * @param  string $paymentCode
     * @param  bool   $isInCatalog
     * @return bool
     */
    public function isMethodQuoteAvailable($paymentCode, $isInCatalog)
    {
        $quote = $isInCatalog ? null : $this->_checkoutSession->getQuote();
        // check payment method availability
        /** @var \Magento\Payment\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $this->_paymentData->getMethodInstance($paymentCode);
        if (!$methodInstance->isAvailable($quote)) {
            return false;
        }
        return true;
    }

    /**
     *  Validates minimum quote amount and zero grand total
     *
     * @param  bool $isInCatalog
     * @return bool
     */
    public function isQuoteSummaryValid($isInCatalog)
    {
        $quote = $isInCatalog ? null : $this->_checkoutSession->getQuote();
        // validate minimum quote amount and validate quote for zero grandtotal
        if (null !== $quote && (!$quote->validateMinimumAmount() || !$quote->getGrandTotal())) {
            return false;
        }
        return true;
    }
}
