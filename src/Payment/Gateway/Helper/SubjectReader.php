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

namespace Amazon\Payment\Gateway\Helper;

use Magento\Checkout\Model\Session;
use Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory;
use Amazon\Core\Helper\Data;
use Magento\Quote\Model\Quote;
use Magento\Payment\Gateway\Helper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class SubjectReader
 * Consolidates commonly used calls
 */
class SubjectReader
{

    /**
     * @var QuoteLinkInterfaceFactory
     */
    private $quoteLinkFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * SubjectReader constructor.
     *
     * @param Session $checkoutSession
     * @param QuoteLinkInterfaceFactory $quoteLinkInterfaceFactory
     * @param Data $coreHelper
     */
    public function __construct(
        Session $checkoutSession,
        QuoteLinkInterfaceFactory $quoteLinkInterfaceFactory,
        Data $coreHelper
    ) {
        $this->quoteLinkFactory = $quoteLinkInterfaceFactory;
        $this->checkoutSession = $checkoutSession;
        $this->coreHelper = $coreHelper;
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
     * Get unique Amazon ID for order from custom table
     *
     * @return mixed
     */
    public function getAmazonId()
    {
        $quoteLink = $this->getQuoteLink();

        return $quoteLink->getAmazonOrderReferenceId();
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return \Amazon\Payment\Model\QuoteLink
     */
    public function getQuoteLink($quote_id = '')
    {
        $quoteLink = $this->quoteLinkFactory->create();

        if (!$quote_id) {
            $quote = $this->getQuote();
            $quoteLink->load($quote->getId(), 'quote_id');
        }
        else {
            $quoteLink->load($quote_id, 'quote_id');
        }
        return $quoteLink;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder() 
    {
        return $this->checkoutSession->getLastRealOrder();
    }
}
