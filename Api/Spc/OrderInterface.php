<?php

namespace Amazon\Pay\Api\Spc;

interface OrderInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return mixed
     */
    public function createOrder(int $cartId, $cartDetails = null);
}
