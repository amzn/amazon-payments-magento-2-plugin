<?php

namespace Amazon\Pay\Api;

interface SpcTaxAndShippingInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $shippingDetails
     * @param mixed|null $cartDetails
     * @return mixed
     */
    public function calculateTaxAndShipping(int $cartId, $shippingDetails = null, $cartDetails = null);
}
