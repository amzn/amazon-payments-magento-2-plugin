<?php
/**
 * Created by PhpStorm.
 * User: miche
 * Date: 7/24/2018
 * Time: 9:53 AM
 */

namespace Amazon\Payment\Plugin;

use Magento\Checkout\Model\Session;

/**
 * Class DisableAmazonPaymentMethod
 * Plugin removes Amazon Payment Method if cart contains only virtual products.
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class DisableAmazonPaymentMethod
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * DisableAmazonPaymentMethod constructor.
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Amazon\Payment\Model\Method\AmazonLoginMethod $subject
     * @param $result
     * @return bool
     */
    public function afterIsAvailable(
        \Amazon\Payment\Model\Method\AmazonLoginMethod $subject,
        $result
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        if ($quote->isVirtual()) {
            return false;
        }

        return $result; // return default result
    }
}
