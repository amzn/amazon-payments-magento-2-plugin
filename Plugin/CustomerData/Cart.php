<?php

namespace Amazon\Pay\Plugin\CustomerData;

use Magento\Checkout\Model\Session;

class Cart
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * Adds virtual cart flag to the cart local storage for button rendering
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    )
    {
        $result['amzn_pay_only'] = $this->checkoutSession->getQuote()->isVirtual();

        return $result;
    }
}
