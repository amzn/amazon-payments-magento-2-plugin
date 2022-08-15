<?php

namespace Amazon\Pay\Api;

interface SpcApplyCouponInterface
{
    /**
     * @param int $cartId
     * @param string $couponCode
     * @return mixed
     */
    public function applyCoupon(int $cartId, string $couponCode);
}
