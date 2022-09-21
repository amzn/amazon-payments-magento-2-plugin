<?php

namespace Amazon\Pay\Api\Spc\Response;

interface DeliveryOptionInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getPrice();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getDiscountedPrice();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\ShippingMethodInterface
     */
    public function getShippingMethod();

    /**
     * @return array
     */
    public function getShippingEstimate();

    /**
     * @return bool
     */
    public function getIsDefault();

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setPrice(AmountInterface $amount);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $discountedPrice
     * @return $this
     */
    public function setDiscountedPrice(AmountInterface $discountedPrice);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\ShippingMethodInterface $shippingMethod
     * @return $this
     */
    public function setShippingMethod(ShippingMethodInterface $shippingMethod);

    /**
     * @param array $shippingEstimate
     * @return $this
     */
    public function setShippingEstimate(array $shippingEstimate);

    /**
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault(bool $isDefault);
}
