<?php

namespace Amazon\Pay\Api\Spc\Response;

interface DeliveryOptionInterface
{
    /**
     * Get id
     *
     * @return string
     */
    public function getId();

    /**
     * Get price
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getPrice();

    /**
     * Get discounted price
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getDiscountedPrice();

    /**
     * Get shipping method
     *
     * @return \Amazon\Pay\Api\Spc\Response\ShippingMethodInterface
     */
    public function getShippingMethod();

    /**
     * Get shipping estimate
     *
     * @return mixed
     */
    public function getShippingEstimate();

    /**
     * Get is default
     *
     * @return bool
     */
    public function getIsDefault();

    /**
     * Set id
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id);

    /**
     * Set price
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setPrice(AmountInterface $amount);

    /**
     * Set discounted price
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $discountedPrice
     * @return $this
     */
    public function setDiscountedPrice(AmountInterface $discountedPrice);

    /**
     * Set shipping method
     *
     * @param \Amazon\Pay\Api\Spc\Response\ShippingMethodInterface $shippingMethod
     * @return $this
     */
    public function setShippingMethod(ShippingMethodInterface $shippingMethod);

    /**
     * Set shipping estimate
     *
     * @param array $shippingEstimate
     * @return $this
     */
    public function setShippingEstimate(array $shippingEstimate);

    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault(bool $isDefault);
}
