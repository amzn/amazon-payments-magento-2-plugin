<?php

namespace Amazon\Pay\Api\Spc;

use Amazon\Pay\Api\Spc\Response\CartDetailsInterface;

interface ShippingMethodInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return CartDetailsInterface
     */
    public function shippingMethod(int $cartId, $cartDetails = null);
}
