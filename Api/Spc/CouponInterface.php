<?php

namespace Amazon\Pay\Api\Spc;

interface CouponInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @return \Amazon\Pay\Api\Spc\ResponseInterface
     */
    public function applyCoupon(int $cartId, $cartDetails = null);
}
