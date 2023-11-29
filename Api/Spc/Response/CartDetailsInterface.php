<?php

namespace Amazon\Pay\Api\Spc\Response;

interface CartDetailsInterface
{
    /**
     * Get cart id
     *
     * @return string
     */
    public function getCartId();

    /**
     * Get line items
     *
     * @return \Amazon\Pay\Api\Spc\Response\LineItemInterface[]
     */
    public function getLineItems();

    /**
     * Get delivery options
     *
     * @return \Amazon\Pay\Api\Spc\Response\DeliveryOptionInterface[]
     */
    public function getDeliveryOptions();

    /**
     * Get coupons
     *
     * @return \Amazon\Pay\Api\Spc\Response\PromoInterface[]
     */
    public function getCoupons();

    /**
     * Get cart language
     *
     * @return string
     */
    public function getCartLanguage();

    /**
     * Get total discount amounts
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalDiscountAmount();

    /**
     * Get total shipping amount
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalShippingAmount();

    /**
     * Get total base amount
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalBaseAmount();

    /**
     * Get total tax amount
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalTaxAmount();

    /**
     * Get total charge amount
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalChargeAmount();

    /**
     * Get checkout session id
     *
     * @return string
     */
    public function getCheckoutSessionId();

    /**
     * Set cart id
     *
     * @param string $cartId
     * @return $this
     */
    public function setCartId(string $cartId);

    /**
     * Set line items
     *
     * @param \Amazon\Pay\Api\Spc\Response\LineItemInterface[] $lineItems
     * @return $this
     */
    public function setLineItems(array $lineItems);

    /**
     * Set delivery options
     *
     * @param \Amazon\Pay\Api\Spc\Response\DeliveryOptionInterface[] $deliveryOptions
     * @return $this
     */
    public function setDeliveryOptions(array $deliveryOptions);

    /**
     * Set coupons
     *
     * @param \Amazon\Pay\Api\Spc\Response\PromoInterface[] $coupons
     * @return $this
     */
    public function setCoupons(array $coupons);

    /**
     * Set cart language
     *
     * @param string $cartLanguage
     * @return $this
     */
    public function setCartLanguage(string $cartLanguage);

    /**
     * Set total discount amount
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalDiscountAmount(AmountInterface $amount);

    /**
     * Set total shipping amount
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalShippingAmount(AmountInterface $amount);

    /**
     * Set total base amount
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalBaseAmount(AmountInterface $amount);

    /**
     * Set total tax amount
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalTaxAmount(AmountInterface $amount);

    /**
     * Set total charge amount
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $amount
     * @return $this
     */
    public function setTotalChargeAmount(AmountInterface $amount);

    /**
     * Set checkout session id
     *
     * @param string $checkoutSessionId
     * @return $this
     */
    public function setCheckoutSessionId(string $checkoutSessionId);
}
