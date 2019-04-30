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

namespace Amazon\Payment\Block;
use Magento\Framework\View\Element\Template;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class CheckoutErrorMessage extends Template
{
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    public function getError() {
        $errorString = '';
        foreach($this->checkoutSession->getQuote()->getErrors() as $error) {
            $errorString .= $error->getText() . "\n";
        }
        return $errorString;
    }

    public function getCheckoutUrl() {
        return $this->getUrl('checkout');
    }
}

