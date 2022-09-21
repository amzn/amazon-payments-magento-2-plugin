<?php

namespace Amazon\Pay\Api\Spc;

use Amazon\Pay\Api\Spc\Response\CartDetailsInterface;

interface CouponInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return CartDetailsInterface
     */
    public function applyCoupon(int $cartId, $cartDetails = null);
}
