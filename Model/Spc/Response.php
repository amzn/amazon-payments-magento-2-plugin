<?php

namespace Amazon\Pay\Model\Spc;

use Magento\Framework\DataObject;
use Amazon\Pay\Api\Spc\ResponseInterface;
use Amazon\Pay\Api\Spc\Response\CartDetailsInterface;

class Response extends DataObject implements ResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getCartDetails()
    {
        return $this->_getData('cart_details');
    }

    /**
     * @inheritDoc
     */
    public function setCartDetails(CartDetailsInterface $cartDetails)
    {
        return $this->setData('cart_details', $cartDetails);
    }
}
