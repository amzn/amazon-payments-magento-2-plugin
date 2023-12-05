<?php

namespace Amazon\Pay\Api\Spc;

interface ResponseInterface
{
    /**
     * Get cart details
     *
     * @return \Amazon\Pay\Api\Spc\Response\CartDetailsInterface
     */
    public function getCartDetails();

    /**
     * Set cart details
     *
     * @param \Amazon\Pay\Api\Spc\Response\CartDetailsInterface $cartDetails
     * @return $this
     */
    public function setCartDetails(Response\CartDetailsInterface $cartDetails);
}
