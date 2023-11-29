<?php

namespace Amazon\Pay\Api\Spc\Response;

interface PromoInterface
{
    /**
     * Get coupon code
     *
     * @return string
     */
    public function getCouponCode();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set coupon code
     *
     * @param string $couponCode
     * @return $this
     */
    public function setCouponCode(string $couponCode);

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description);
}
