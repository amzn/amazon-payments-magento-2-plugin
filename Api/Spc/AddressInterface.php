<?php

namespace Amazon\Pay\Api\Spc;

use Amazon\Pay\Api\Spc\Response\CartDetailsInterface;

interface AddressInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return CartDetailsInterface
     */
    public function saveAddress(int $cartId, $cartDetails = null);
}
