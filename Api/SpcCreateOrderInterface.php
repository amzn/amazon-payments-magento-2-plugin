<?php

namespace Amazon\Pay\Api;

interface SpcCreateOrderInterface
{
    /**
     * @param int $cartId
     * @param string $checkoutSessionId
     * @return mixed
     */
    public function createOrder(int $cartId, string $checkoutSessionId);
}
