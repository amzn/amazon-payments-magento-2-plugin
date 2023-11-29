<?php

namespace Amazon\Pay\Api\Spc\Response;

interface AmountInterface
{
    /**
     * Get decimal amount
     *
     * @return string
     */
    public function getAmount();

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Set decimal amount
     *
     * @param string $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Set currency count
     *
     * @param string $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode);
}
