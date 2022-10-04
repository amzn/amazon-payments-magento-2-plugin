<?php

namespace Amazon\Pay\Api\Spc;

interface ShippingMethodInterface
{
    /**
     * @param int $cartId
     * @param mixed|null $cartDetails
     * @param bool $skipSave
     * @return \Amazon\Pay\Api\Spc\ResponseInterface|bool
     */
    public function shippingMethod(int $cartId, $cartDetails = null, $skipSave = false);
}
