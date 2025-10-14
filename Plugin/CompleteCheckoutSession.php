<?php

declare(strict_types=1);

namespace Amazon\Pay\Plugin;

use Amazon\Pay\Api\CheckoutSessionManagementInterface;
use Amazon\Pay\Gateway\Config\Config;
use Closure;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;

class CompleteCheckoutSession
{
    public function __construct(
        private CheckoutSessionManagementInterface $checkoutSessionManagement,
    ) {
    }

    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        Closure $proceed,
        int $cartId,
        PaymentInterface $payment = null
    ): int {
        $result = (int)$proceed($cartId, $payment);

        if (!$payment || $payment->getMethod() !== Config::CODE) {
            return $result;
        }

        $this->checkoutSessionManagement->completeCheckoutSession(
            $payment->getAdditionalData()['amazon_session_id'], 
            $cartId, 
            $result,
            true // better to have a new config value to choose flow type to complete
        );

        return $result;
    }
}
