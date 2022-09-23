<?php

namespace Amazon\Pay\Api\Spc;

interface ShippingMethodInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return ResponseInterface
     */
    public function shippingMethod(int $cartId, $cartDetails = null);
}
