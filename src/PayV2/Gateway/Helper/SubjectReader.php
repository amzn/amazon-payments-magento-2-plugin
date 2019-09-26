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

namespace Amazon\PayV2\Gateway\Helper;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Payment\Gateway\Helper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Amazon\PayV2\CustomerData\CheckoutSession;
use Amazon\Core\Model\AmazonConfig;

/**
 * Class SubjectReader
 * Consolidates commonly used calls
 */
class SubjectReader
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Session
     */
    private $amazonCheckoutSession;

    /**
     * SubjectReader constructor.
     * @param Session $checkoutSession
     * @param CheckoutSession $amazonCheckoutSession
     */
    public function __construct(
        AmazonConfig $amazonConfig,
        Session $checkoutSession,
        CheckoutSession $amazonCheckoutSession
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->checkoutSession = $checkoutSession;
        $this->amazonCheckoutSession = $amazonCheckoutSession;
    }

    /**
     * Reads payment from subject
     *
     * @param  array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads amount from subject
     *
     * @param  array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Gets quote from current checkout session and returns store ID
     *
     * @return int
     */
    public function getStoreId()
    {
        $quote = $this->getQuote();

        return $quote->getStoreId();
    }

    /**
     * Get Amazon Checkout Session ID
     *
     * @return mixed
     */
    public function getAmazonCheckoutSessionId()
    {
        return $this->amazonCheckoutSession->getCheckoutSessionId();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getCheckoutQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getCheckoutOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function getAmazonConfig()
    {
        return $this->amazonConfig;
    }
}
