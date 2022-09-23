<?php

namespace Amazon\Pay\Api\Spc;

interface CouponInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return ResponseInterface
     */
    public function applyCoupon(int $cartId, $cartDetails = null);
}
