<?php

namespace Amazon\Pay\Api\Spc\Response;

interface CartDetailsInterface
{
    /**
     * @return string
     */
    public function getCartId();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\LineItemInterface[]
     */
    public function getLineItems();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\DeliveryOptionInterface[]
     */
    public function getDeliveryOptions();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\PromoInterface[]
     */
    public function getCoupons();

    /**
     * @return string
     */
    public function getCartLanguage();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalDiscountAmount();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalShippingAmount();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalBaseAmount();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalTaxAmount();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalChargeAmount();

    /**
     * @return string
     */
    public function getCheckoutSessionId();

    /**
     * @param string $cartId
     * @return $this
     */
    public function setCartId(string $cartId);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\LineItemInterface[]
     * @return $this
     */
    public function setLineItems(array $lineItems);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\DeliveryOptionInterface[] $deliveryOptions
     * @return $this
     */
    public function setDeliveryOptions(array $deliveryOptions);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\PromoInterface[] $coupons
     * @return $this
     */
    public function setCoupons(array $coupons);

    /**
     * @param string $cartLanguage
     * @return $this
     */
    public function setCartLanguage(string $cartLanguage);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalDiscountAmount(AmountInterface $amount);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalShippingAmount(AmountInterface $amount);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalBaseAmount(AmountInterface $amount);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalTaxAmount(AmountInterface $amount);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalChargeAmount(AmountInterface $amount);

    /**
     * @param string $checkoutSessionId
     * @return $this
     */
    public function setCheckoutSessionId(string $checkoutSessionId);
}
