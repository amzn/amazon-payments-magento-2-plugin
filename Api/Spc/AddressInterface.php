<?php

namespace Amazon\Pay\Api\Spc;

interface AddressInterface
{
    /**
     * Save address
     *
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return \Amazon\Pay\Api\Spc\ResponseInterface
     */
    public function saveAddress(int $cartId, $cartDetails = null);
}
