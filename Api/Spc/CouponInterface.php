<?php

namespace Amazon\Pay\Api\Spc;

interface CouponInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return mixed
     */
    public function applyCoupon(int $cartId, $cartDetails = null);
}
