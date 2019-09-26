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

namespace Amazon\Payment\Plugin;

use Amazon\Core\Exception\AmazonWebapiException;
use Amazon\Payment\Api\Data\QuoteLinkInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Amazon\Payment\Model\Adapter\AmazonPaymentAdapter;
use Amazon\Payment\Model\OrderInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Amazon\Payment\Gateway\Config\Config as GatewayConfig;
use Magento\Quote\Api\CartRepositoryInterface;


/**
 * Class ConfirmOrderReference
 *
 * Confirm the OrderReference when payment details are saved
 */
class ConfirmOrderReference
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderInformationManagement
     */
    private $orderInformationManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * ConfirmOrderReference constructor.
     * @param Session $checkoutSession
     * @param OrderInformationManagement $orderInformationManagement
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Session $checkoutSession,
        OrderInformationManagement $orderInformationManagement,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderInformationManagement = $orderInformationManagement;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param PaymentMethodManagementInterface $subject
     * @param $result
     * @param $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSet(
        PaymentMethodManagementInterface $subject,
        $result,
        $cartId,
        PaymentInterface $paymentMethod
    ) {
        if($paymentMethod->getMethod() == GatewayConfig::CODE) {
            $quote = $this->quoteRepository->get($cartId);
            $quoteExtensionAttributes = $quote->getExtensionAttributes();
            if ($quoteExtensionAttributes) {
                $amazonOrderReferenceId = $quoteExtensionAttributes
                    ->getAmazonOrderReferenceId()
                    ->getAmazonOrderReferenceId();

                $this->orderInformationManagement->saveOrderInformation($amazonOrderReferenceId);
                $this->orderInformationManagement->confirmOrderReference(
                    $amazonOrderReferenceId,
                    $quote->getStoreId()
                );
            }
        }

        return $result;
    }
}
