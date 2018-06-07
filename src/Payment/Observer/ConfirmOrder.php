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
namespace Amazon\Payment\Observer;

use Amazon\Core\Helper\Data;
use Amazon\Core\Exception\AmazonWebapiException;
use Amazon\Core\Helper\CategoryExclusion;
use Amazon\Payment\Api\Data\QuoteLinkInterface;
use Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory;
use Amazon\Payment\Domain\AmazonAuthorizationStatus;
use Amazon\Payment\Model\Method\Amazon;
use Amazon\Payment\Model\OrderInformationManagement;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Sales\Model\Order;

/**
 * Class ConfirmOrder
 * @package Amazon\Payment\Observer
 */
class ConfirmOrder implements ObserverInterface
{
    /**
     * @var QuoteLinkInterfaceFactory
     */
    private $quoteLinkFactory;

    /**
     * @var OrderInformationManagement
     */
    private $orderInformationManagement;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CategoryExclusion
     */
    private $categoryExclusionHelper;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * ConfirmOrder constructor.
     *
     * @param QuoteLinkInterfaceFactory        $quoteLinkFactory
     * @param OrderInformationManagement       $orderInformationManagement
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CategoryExclusion                $categoryExclusionHelper
     */
    public function __construct(
        QuoteLinkInterfaceFactory $quoteLinkFactory,
        OrderInformationManagement $orderInformationManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CategoryExclusion $categoryExclusionHelper,
        Data $coreHelper
    ) {
        $this->quoteLinkFactory           = $quoteLinkFactory;
        $this->orderInformationManagement = $orderInformationManagement;
        $this->paymentMethodManagement    = $paymentMethodManagement;
        $this->categoryExclusionHelper    = $categoryExclusionHelper;
        $this->coreHelper                 = $coreHelper;
    }

    /**
     * @param Observer $observer
     * @throws AmazonWebapiException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $order                  = $observer->getOrder();
            $quoteId                = $order->getQuoteId();
            $storeId                = $order->getStoreId();
            $quoteLink              = $this->getQuoteLink($quoteId);
            $amazonOrderReferenceId = $quoteLink->getAmazonOrderReferenceId();

            if ($amazonOrderReferenceId) {
                $payment = $this->paymentMethodManagement->get($quoteId);
                if (Amazon::PAYMENT_METHOD_CODE == $payment->getMethod()) {
                    $this->checkForExcludedProducts();
                    $this->saveOrderInformation($quoteLink, $amazonOrderReferenceId, $order);
                    $this->confirmOrderReference($quoteLink, $amazonOrderReferenceId, $storeId);
                }
            }
        }
    }

    /**
     * @throws AmazonWebapiException
     */
    protected function checkForExcludedProducts()
    {
        if ($this->categoryExclusionHelper->isQuoteDirty()) {
            throw new AmazonWebapiException(
                __(
                    'Unfortunately it is not possible to pay with Amazon Pay for this order. ' .
                    'Please choose another payment method.'
                ),
                AmazonAuthorizationStatus::CODE_HARD_DECLINE,
                AmazonWebapiException::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * @param QuoteLinkInterface $quoteLink
     * @param $amazonOrderReferenceId
     * @param Order\Interceptor $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveOrderInformation(
        QuoteLinkInterface $quoteLink,
        $amazonOrderReferenceId,
        \Magento\Sales\Model\Order\Interceptor $order
    ) {
        if (! $quoteLink->isConfirmed()) {
            $this->orderInformationManagement->saveOrderInformation(
                $amazonOrderReferenceId,
                [],
                $order->getIncrementId()
            );
        }
    }

    /**
     * @param QuoteLinkInterface $quoteLink
     * @param $amazonOrderReferenceId
     * @param $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function confirmOrderReference(QuoteLinkInterface $quoteLink, $amazonOrderReferenceId, $storeId)
    {
        $this->orderInformationManagement->confirmOrderReference($amazonOrderReferenceId, $storeId);
        $quoteLink->setConfirmed(true)->save();
    }

    /**
     * @param $quoteId
     * @return QuoteLinkInterface
     */
    protected function getQuoteLink($quoteId)
    {
        $quoteLink = $this->quoteLinkFactory->create();
        $quoteLink->load($quoteId, 'quote_id');

        return $quoteLink;
    }
}
