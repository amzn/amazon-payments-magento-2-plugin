<?php

namespace Amazon\Pay\Api\Spc\Response;

interface LineItemInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getQuantity();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getListPrice();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface
     */
    public function getDiscountedPrice();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\PromoInterface[]
     */
    public function getAppliedDiscounts();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\NameValueInterface[]
     */
    public function getAdditionalAttributes();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return \Amazon\Pay\Api\Spc\Response\AmountInterface[]
     */
    public function getTaxAmount();

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id);

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title);

    /**
     * @param string $quantity
     * @return $this
     */
    public function setQuantity(string $quantity);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $listPrice
     * @return $this
     */
    public function setListPrice(AmountInterface $listPrice);

    /**
     * @param \Amazon\Pay\Api\Spc\Response\AmountInterface $discountedPrice
     * @return $this
     */
    public function setDiscountedPrice(AmountInterface $discountedPrice);

    /**
     * @param array $additionalAttributes
     * @return $this
     */
    public function setAdditionalAttributes(array $additionalAttributes);

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status);

    /**
     * @param array $taxAmount
     * @return $this
     */
    public function setTaxAmount(array $taxAmount);
}
