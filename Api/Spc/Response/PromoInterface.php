<?php

namespace Amazon\Pay\Api\Spc\Response;

interface PromoInterface
{
    /**
     * @return string
     */
    public function getCouponCode();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $couponCode
     * @return $this
     */
    public function setCouponCode(string $couponCode);

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description);
}
