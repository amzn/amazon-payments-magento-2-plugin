<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\AmountInterface;
use Amazon\Pay\Api\Spc\Response\DeliveryOptionInterface;
use Amazon\Pay\Api\Spc\Response\ShippingMethodInterface;
use Magento\Framework\DataObject;

class DeliveryOption extends DataObject implements DeliveryOptionInterface
{
    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->_getData('id');
    }

    /**
     * @inheritDoc
     */
    public function getPrice()
    {
        return $this->_getData('price');
    }

    /**
     * @inheritDoc
     */
    public function getDiscountedPrice()
    {
        return $this->_getData('discounted_price');
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethod()
    {
        return $this->_getData('shipping_method');
    }

    /**
     * @inheritDoc
     */
    public function getShippingEstimate()
    {
        return $this->_getData('shipping_estimate');
    }

    /**
     * @inheritDoc
     */
    public function getIsDefault()
    {
        return $this->_getData('is_default');
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id)
    {
        return $this->setData('id', $id);
    }

    /**
     * @inheritDoc
     */
    public function setPrice(AmountInterface $amount)
    {
        return $this->setData('price', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountedPrice(AmountInterface $discountedPrice)
    {
        return $this->setData('discounted_price', $discountedPrice);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethod(ShippingMethodInterface $shippingMethod)
    {
        return $this->setData('shipping_method', $shippingMethod);
    }

    /**
     * @inheritDoc
     */
    public function setShippingEstimate(array $shippingEstimate)
    {
        return $this->setData('shipping_estimate', $shippingEstimate);
    }

    /**
     * @inheritDoc
     */
    public function setIsDefault(bool $isDefault)
    {
        return $this->setData('is_default', $isDefault);
    }
}
