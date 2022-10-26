<?php

namespace Amazon\Pay\Api\Spc;

interface ResponseInterface
{
    /**
     * @return \Amazon\Pay\Api\Spc\Response\CartDetailsInterface
     */
    public function getCartDetails();

    /**
     * @param \Amazon\Pay\Api\Spc\Response\CartDetailsInterface $cartDetails
     * @return $this
     */
    public function setCartDetails(Response\CartDetailsInterface $cartDetails);
}
