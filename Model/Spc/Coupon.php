<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\CouponInterface;
use Amazon\Pay\Helper\Spc\CheckoutSession;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Magento\Directory\Model\Currency;

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
     * @var Currency
     */
    protected $currency;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSessionHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param Cart $cartHelper
     * @param Currency $currency
     * @param CheckoutSession $checkoutSessionHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonPayAdapter,
        Cart $cartHelper,
        Currency $currency,
        CheckoutSession $checkoutSessionHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->cartHelper = $cartHelper;
        $this->currency = $currency;
        $this->checkoutSessionHelper = $checkoutSessionHelper;
    }

    /**
     * @inheritdoc
     */
    public function applyCoupon(int $cartId, $cartDetails = null)
    {
        // Get quote
        try {
            /** @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->cartRepository->getActive($cartId);
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC Coupon: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase('InvalidCartId'), "Cart Id ". $cartId ." not found or inactive", 404
            );
        }

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Get checkout session for verification
        if ($cartDetails && $checkoutSessionId) {
            if ($this->checkoutSessionHelper->confirmCheckoutSession($quote, $cartDetails, $checkoutSessionId)) {
                // Only grabbing the first one, as Magento only accepts one coupon code
                if (isset($cartDetails['coupons'][0]['coupon_code'])) {
                    $couponCode = $cartDetails['coupons'][0]['coupon_code'];

                    // TODO: Improve on keeping the correct currency code for multi-currency stores
                    // Magento changes it when the store's currency doesn't match the quote's currency on API calls
                    $quoteCurrency = $this->currency->load($quote->getQuoteCurrencyCode());
                    $quote->setForcedCurrency($quoteCurrency);

                    // Attempt to set coupon code
                    $quote->setCouponCode($couponCode);

                    // Save cart
                    $this->cartRepository->save($quote);

                    // Check if the coupon was applied
                    if ($quote->getCouponCode() != $couponCode) {
                        $this->cartHelper->logError(
                            'SPC Coupon: CouponNotApplicable - The coupon could not be applied to the cart. CartId: ' . $cartId . ' - ', $cartDetails
                        );

                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase('CouponNotApplicable'), "The coupon code ". $couponCode ." does not apply", 400
                        );
                    }
                }
            }
        }

        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }
}
