<?php

namespace Amazon\Pay\Api\Spc\Response;

interface ShippingMethodInterface
{
    /**
     * Get shipping method name
     *
     * @return string
     */
    public function getShippingMethodName();

    /**
     * Get shipping method code
     *
     * @return string
     */
    public function getShippingMethodCode();

    /**
     * Set shipping method name
     *
     * @param string $shippingMethodName
     * @return $this
     */
    public function setShippingMethodName(string $shippingMethodName);

    /**
     * Set shipping method code
     *
     * @param string $shippingMethodCode
     * @return $this
     */
    public function setShippingMethodCode(string $shippingMethodCode);
}
