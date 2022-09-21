<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\AmountInterface;
use Magento\Framework\DataObject;

class Amount extends DataObject implements AmountInterface
{
    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->_getData('amount');
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyCode()
    {
        return $this->_getData('currencyCode');
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        return $this->setData('amount', $amount);
    }

    /**
     * @inheritDoc
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData('currencyCode', $currencyCode);
    }
}
