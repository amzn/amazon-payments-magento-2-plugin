<?php
/**
 * Created by PhpStorm.
 * User: miche
 * Date: 7/24/2018
 * Time: 9:53 AM
 */

namespace Amazon\Payment\Plugin;

use Magento\Checkout\Model\Session;

class DisableAmazonPaymentMethod
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * DisableAmazonPaymentMethod constructor.
     * @param Session $checkoutSession
     * @param Quote $quoteSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Paypal\Model\Express $subject
     * @param $result
     * @return bool
     */
    public function afterIsAvailable(
        \Amazon\Payment\Model\Method\AmazonLoginMethod $subject,
        $result
    )
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        if ($quote->isVirtual()) {
            return false;
        }

        return $result; // return default result
    }
}
