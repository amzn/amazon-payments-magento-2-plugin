<?php

namespace Amazon\Pay\Api\Spc;

interface AddressInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $shippingDetails
     * @param mixed|null $cartDetails
     * @return mixed
     */
    public function saveAddress(int $cartId, $shippingDetails = null, $cartDetails = null);
}
