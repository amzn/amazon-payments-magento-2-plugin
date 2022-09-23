<?php

namespace Amazon\Pay\Api\Spc;

interface OrderInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return ResponseInterface
     */
    public function createOrder(int $cartId, $cartDetails = null);
}
