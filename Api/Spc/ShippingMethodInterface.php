<?php

namespace Amazon\Pay\Api\Spc;

interface ShippingMethodInterface
{
    /**
     * @param int $cartId
     * @param mixed $shippingMethod
     * @return mixed
     */
    public function shippingMethod(int $cartId, $shippingMethod);
}
