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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class Validate extends Template
{
    /**
     * Build and return 'forgot password' URL
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/forgotpassword');
    }

    /**
     * Create guest checkout URL with checkout session ID param
     *
     * @return string
     */
    public function getContinueAsGuestUrl()
    {
        $checkoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');
        return $this->_urlBuilder->getUrl('checkout', ['_query' => ['amazonCheckoutSessionId' => $checkoutSessionId]]);
    }

    /**
     * Return true if guest checkout is allowed
     *
     * @return bool
     */
    public function isGuestCheckoutEnabled()
    {
        return $this->_scopeConfig->getValue('checkout/options/guest_checkout');
    }
}
