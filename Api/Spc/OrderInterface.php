<?php

namespace Amazon\Pay\Api\Spc;

interface OrderInterface
{
    /**
     * Create order
     *
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return \Amazon\Pay\Api\Spc\ResponseInterface
     */
    public function createOrder(int $cartId, $cartDetails = null);
}
