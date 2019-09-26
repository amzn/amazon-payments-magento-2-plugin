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

namespace Amazon\PayV2\Plugin;

use Amazon\PayV2\Gateway\Config\Config as GatewayConfig;

class SaveGuestPaymentInformation
{
    /**
     * @var \Amazon\PayV2\Model\CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * @var \Amazon\PayV2\CustomerData\CheckoutSession
     */
    private $amazonCheckoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * SaveGuestPaymentInformation constructor.
     * @param \Amazon\PayV2\Model\CheckoutSessionManagement $checkoutSessionManagement
     * @param \Amazon\PayV2\CustomerData\CheckoutSession $amazonCheckoutSession
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Amazon\PayV2\Model\CheckoutSessionManagement $checkoutSessionManagement,
        \Amazon\PayV2\CustomerData\CheckoutSession $amazonCheckoutSession,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->checkoutSessionManagement = $checkoutSessionManagement;
        $this->amazonCheckoutSession = $amazonCheckoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
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
    public function afterSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $result,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    ) {
        if ($paymentMethod->getMethod() == GatewayConfig::CODE) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            /** @var Quote $quote */
            $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

            return $this->checkoutSessionManagement->updateCheckoutSession(
                $quote,
                $this->amazonCheckoutSession->getCheckoutSessionId()
            );

        }
        return $result;
    }
}
