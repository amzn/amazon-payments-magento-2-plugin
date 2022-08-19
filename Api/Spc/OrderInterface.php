<?php

namespace Amazon\Pay\Api\Spc;

interface OrderInterface
{
    /**
     * @param int $cartId
     * @param string $checkoutSessionId
     * @return mixed
     */
    public function createOrder(int $cartId, string $checkoutSessionId);
}
