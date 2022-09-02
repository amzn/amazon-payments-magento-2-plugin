<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\CouponInterface;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Quote\Api\CartRepositoryInterface;
use Amazon\Pay\Helper\Spc\Cart;

class Coupon implements CouponInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var AmazonPayAdapter
     */
    protected $amazonPayAdapter;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param Cart $cartHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonPayAdapter,
        Cart $cartHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @inheritdoc
     */
    public function applyCoupon(int $cartId, $cartDetails = null)
    {
        // Get quote
        $quote = $this->cartRepository->get($cartId);

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkoutSessionId'] ?? null;

        // Get checkout session for verification
        if ($cartDetails && $checkoutSessionId) {
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession($quote->getStoreId(), $checkoutSessionId);

            $amazonSessionStatus = $amazonSession['status'] ?? '404';
            if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
                throw new WebapiException(
                    new Phrase($amazonSession['reasonCode'])
                );
            }

            if ($amazonSession['statusDetails']['state'] !== 'Open') {
                throw new WebapiException(
                    new Phrase($amazonSession['statusDetails']['reasonCode'])
                );
            }

            // Only grabbing the first one, as Magento only accepts one coupon code
            if (isset($cartDetails['coupons'][0]['couponCode'])) {
                $couponCode = $cartDetails['coupons'][0]['couponCode'];

                // Attempt to set coupon code
                $quote->setCouponCode($couponCode);

                // Save cart
                $this->cartRepository->save($quote);

                // Check if the coupon was applied
                if ($quote->getCouponCode() != $couponCode) {
                    throw new WebapiException(
                        new Phrase('CouponNotApplicable')
                    );
                }
            }
        }

        return $this->cartHelper->createResponse($quote, $checkoutSessionId);
    }
}
