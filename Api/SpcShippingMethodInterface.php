<?php

namespace Amazon\Pay\Api;

interface SpcShippingMethodInterface
{
    /**
     * @param int $cartId
     * @param mixed $shippingMethod
     * @return mixed
     */
    public function shippingMethod(int $cartId, $shippingMethod);
}
