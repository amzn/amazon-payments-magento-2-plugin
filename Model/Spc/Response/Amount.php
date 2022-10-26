<?php

namespace Amazon\Pay\Model\Spc\Response;

use Amazon\Pay\Api\Spc\Response\AmountInterface;
use Magento\Framework\DataObject;

class Amount extends DataObject implements AmountInterface
{
    protected $localeFormat;

    public function __construct(
        \Magento\Framework\Locale\Format $localeFormat,
        array $data = []
    )
    {
        parent::__construct($data);

        $this->localeFormat = $localeFormat;
    }

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
        // Built in currency formatter is arbitrarily adding currency symbol.
        // Went with a more direct approach, using the local format and passing it to number_format
        $format = $this->localeFormat->getPriceFormat();
        $formattedAmount = number_format($amount, $format['precision'], $format['decimalSymbol'], $format['groupSymbol']);

        return $this->setData('amount', $formattedAmount);
    }

    /**
     * @inheritDoc
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData('currencyCode', $currencyCode);
    }
}
