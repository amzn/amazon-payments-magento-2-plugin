<?php

namespace Amazon\Pay\Api\Spc\Response;

interface AmountInterface
{
    /**
     * @return string
     */
    public function getAmount();

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @param string $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * @param string $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode);
}
