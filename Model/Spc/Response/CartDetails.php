<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\AmountInterface;
use Amazon\Pay\Api\Spc\Response\CartDetailsInterface;
use Magento\Framework\DataObject;

class CartDetails extends DataObject implements CartDetailsInterface
{
    /**
     * @inheritDoc
     */
    public function getCartId()
    {
        return $this->_getData('cart_id');
    }

    /**
     * @inheritDoc
     */
    public function getLineItems()
    {
        return $this->_getData('line_items');
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryOptions()
    {
        return $this->_getData('delivery_options');
    }

    /**
     * @inheritDoc
     */
    public function getCoupons()
    {
        return $this->_getData('coupons');
    }

    /**
     * @inheritDoc
     */
    public function getCartLanguage()
    {
        return $this->_getData('cart_language');
    }

    /**
     * @inheritDoc
     */
    public function getTotalDiscountAmount()
    {
        return $this->_getData('total_discount_amount');
    }

    /**
     * @inheritDoc
     */
    public function getTotalShippingAmount()
    {
        return $this->_getData('total_shipping_amount');
    }

    /**
     * @inheritDoc
     */
    public function getTotalBaseAmount()
    {
        return $this->_getData('total_base_amount');
    }

    /**
     * @inheritDoc
     */
    public function getTotalBaseInclTaxAmount()
    {
        return $this->_getData('total_base_incl_tax_amount');
    }

    /**
     * @inheritDoc
     */
    public function getTotalTaxAmount()
    {
        return $this->_getData('total_tax_amount');
    }

    /**
     * @inheritDoc
     */
    public function getTotalChargeAmount()
    {
        return $this->_getData('total_charge_amount');
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutSessionId()
    {
        return $this->_getData('checkout_session_id');
    }

    /**
     * @inheritDoc
     */
    public function setCartId(string $cartId)
    {
        return $this->setData('cart_id', $cartId);
    }

    /**
     * @inheritDoc
     */
    public function setLineItems(array $lineItems)
    {
        return $this->setData('line_items', $lineItems);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryOptions(array $deliveryOptions)
    {
        return $this->setData('delivery_options', $deliveryOptions);
    }

    /**
     * @inheritDoc
     */
    public function setCoupons(array $coupons)
    {
        return $this->setData('coupons', $coupons);
    }

    /**
     * @inheritDoc
     */
    public function setCartLanguage(string $cartLanguage)
    {
        return $this->setData('cart_language', $cartLanguage);
    }

    /**
     * @inheritDoc
     */
    public function setTotalDiscountAmount(AmountInterface $amount)
    {
        return $this->setData('total_discount_amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setTotalShippingAmount(AmountInterface $amount)
    {
        return $this->setData('total_shipping_amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setTotalBaseAmount(AmountInterface $amount)
    {
        return $this->setData('total_base_amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setTotalBaseInclTaxAmount(AmountInterface $amount)
    {
        return $this->setData('total_base_incl_tax_amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setTotalTaxAmount(AmountInterface $amount)
    {
        return $this->setData('total_tax_amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setTotalChargeAmount(AmountInterface $amount)
    {
        return $this->setData('total_charge_amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setCheckoutSessionId(string $checkoutSessionId)
    {
        return $this->setData('checkout_session_id', $checkoutSessionId);
    }
}
