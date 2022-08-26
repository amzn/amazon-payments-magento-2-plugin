<?php

namespace Amazon\Pay\Api\Spc;

interface AddressInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return mixed
     */
    public function saveAddress(int $cartId, $cartDetails = null);
}
