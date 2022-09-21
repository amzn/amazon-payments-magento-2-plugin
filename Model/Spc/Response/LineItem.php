<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\AmountInterface;
use Amazon\Pay\Api\Spc\Response\LineItemInterface;
use Amazon\Pay\Api\Spc\Response\NameValueInterface;
use Amazon\Pay\Api\Spc\Response\PromoInterface;
use Magento\Framework\DataObject;

class LineItem extends DataObject implements LineItemInterface
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
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * @inheritDoc
     */
    public function getQuantity()
    {
        return $this->_getData('quantity');
    }

    /**
     * @inheritDoc
     */
    public function getListPrice()
    {
        return $this->_getData('list_price');
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
    public function getAppliedDiscounts()
    {
        return $this->_getData('applied_discounts');
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalAttributes()
    {
        return $this->_getData('additional_attributes');
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_getData('status');
    }

    /**
     * @inheritDoc
     */
    public function getTaxAmount()
    {
        return $this->_getData('tax_amount');
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
    public function setTitle(string $title)
    {
        return $this->setData('title', $title);
    }

    /**
     * @inheritDoc
     */
    public function setQuantity(string $quantity)
    {
        return $this->setData('quantity', $quantity);
    }

    /**
     * @inheritDoc
     */
    public function setListPrice(AmountInterface $listPrice)
    {
        return $this->setData('list_price', $listPrice);
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
    public function setAdditionalAttributes(array $additionalAttributes)
    {
        return $this->setData('additional_attributes', $additionalAttributes);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status)
    {
        return $this->setData('status', $status);
    }

    /**
     * @inheritDoc
     */
    public function setTaxAmount(array $taxAmount)
    {
        return $this->setData('tax_amount', $taxAmount);
    }
}
