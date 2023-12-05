<?php

namespace Amazon\Pay\Api\Spc\Response;

interface LineItemInterface
{
    /**
     * Get id
     *
     * @return string
     */
    public function getId();

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity();

    /**
     * Get list price
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getListPrice();

    /**
     * Get total list price
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getTotalListPrice();

    /**
     * Get discounted price
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getDiscountedPrice();

    /**
     * Get applied discounts
     *
     * @return \Amazon\Pay\Api\Spc\Response\PromoInterface[]
     */
    public function getAppliedDiscounts();

    /**
     * Get additional attributes
     *
     * @return \Amazon\Pay\Api\Spc\Response\NameValueInterface[]
     */
    public function getAdditionalAttributes();

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get tax amount
     *
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface[]
     */
    public function getTaxAmount();

    /**
     * Set id
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id);

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title);

    /**
     * Set quantity
     *
     * @param string $quantity
     * @return $this
     */
    public function setQuantity(string $quantity);

    /**
     * Set list price
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $listPrice
     * @return $this
     */
    public function setListPrice(AmountInterface $listPrice);

    /**
     * Set total list price
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $listPrice
     * @return $this
     */
    public function setTotalListPrice(AmountInterface $listPrice);

    /**
     * Set discounted price
     *
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $discountedPrice
     * @return $this
     */
    public function setDiscountedPrice(AmountInterface $discountedPrice);

    /**
     * Set applied discounts
     *
     * @param \Amazon\Pay\Api\Spc\Response\PromoInterface[] $appliedDiscounts
     * @return $this
     */
    public function setAppliedDiscounts(array $appliedDiscounts);

    /**
     * Set additional attributes
     *
     * @param array $additionalAttributes
     * @return $this
     */
    public function setAdditionalAttributes(array $additionalAttributes);

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status);

    /**
     * Set tax amount
     *
     * @param array $taxAmount
     * @return $this
     */
    public function setTaxAmount(array $taxAmount);
}
