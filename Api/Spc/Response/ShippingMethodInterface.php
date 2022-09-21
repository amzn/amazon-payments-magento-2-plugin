<?php

namespace Amazon\Pay\Api\Spc\Response;

interface ShippingMethodInterface
{
    /**
     * @return string
     */
    public function getShippingMethodName();

    /**
     * @return string
     */
    public function getShippingMethodCode();

    /**
     * @param string $shippingMethodName
     * @return $this
     */
    public function setShippingMethodName(string $shippingMethodName);

    /**
     * @param string $shippingMethodCode
     * @return $this
     */
    public function setShippingMethodCode(string $shippingMethodCode);
}
