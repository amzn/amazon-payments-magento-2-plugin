<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\ShippingMethodInterface;
use Magento\Framework\DataObject;

class ShippingMethod extends DataObject implements ShippingMethodInterface
{
    /**
     * @inheritDoc
     */
    public function getShippingMethodName()
    {
        return $this->_getData('shipping_method_name');
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethodCode()
    {
        return $this->_getData('shipping_method_code');
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethodName(string $shippingMethodName)
    {
        return $this->setData('shipping_method_name', $shippingMethodName);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethodCode(string $shippingMethodCode)
    {
        return $this->setData('shipping_method_code', $shippingMethodCode);
    }
}
