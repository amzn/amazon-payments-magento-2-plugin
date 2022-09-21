<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\PromoInterface;
use Magento\Framework\DataObject;

class Promo extends DataObject implements PromoInterface
{
    /**
     * @inheritDoc
     */
    public function getCouponCode()
    {
        return $this->_getData('coupon_code');
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->_getData('description');
    }

    /**
     * @inheritDoc
     */
    public function setCouponCode(string $couponCode)
    {
        return $this->setData('coupon_code', $couponCode);
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description)
    {
        return $this->setData('description', $description);
    }
}
