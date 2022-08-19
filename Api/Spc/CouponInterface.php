<?php

namespace Amazon\Pay\Api\Spc;

interface CouponInterface
{
    /**
     * @param int $cartId
     * @param string $couponCode
     * @return mixed
     */
    public function applyCoupon(int $cartId, string $couponCode);
}
