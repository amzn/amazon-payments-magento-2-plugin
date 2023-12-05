<?php

namespace Amazon\Pay\Api\Spc;

interface CouponInterface
{
    /**
     * Apply coupon
     *
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return \Amazon\Pay\Api\Spc\ResponseInterface
     */
    public function applyCoupon(int $cartId, $cartDetails = null);
}
