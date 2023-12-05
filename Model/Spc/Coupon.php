<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\CouponInterface;
use Amazon\Pay\Helper\Spc\CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Magento\Store\Api\Data\StoreInterface;

class Coupon implements CouponInterface
{
    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
    /**
     * @var CheckoutSession
     */
    protected $checkoutSessionHelper;

    /**
     * @param StoreInterface $store
     * @param CartRepositoryInterface $cartRepository
     * @param Cart $cartHelper
     * @param CheckoutSession $checkoutSessionHelper
     */
    public function __construct(
        StoreInterface $store,
        CartRepositoryInterface $cartRepository,
        Cart $cartHelper,
        CheckoutSession $checkoutSessionHelper
    ) {
        $this->store = $store;
        $this->cartRepository = $cartRepository;
        $this->cartHelper = $cartHelper;
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

            // Set currency on the http context
            $this->store->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC Coupon: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase("Cart Id ". $cartId ." not found or inactive"),
                "InvalidCartId",
                404
            );
        }

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Get checkout session for verification
        if ($cartDetails && $checkoutSessionId) {
            if ($this->checkoutSessionHelper->confirmCheckoutSession($quote, $cartDetails, $checkoutSessionId)) {
                // Only grabbing the first one, as Magento only accepts one coupon code
                if (isset($cartDetails['coupons'][0]['coupon_code'])) {
                    $previousCode = $quote->getCouponCode();

                    $couponCode = $cartDetails['coupons'][0]['coupon_code'];

                    // Empty out the quote items' rule ids, because of Magento bug
                    foreach ($quote->getItems() as &$item) {
                        $item->setAppliedRuleIds(null);
                    }

                    // Attempt to set coupon code
                    $quote->setCouponCode($couponCode);

                    // Save cart
                    $this->cartRepository->save($quote);

                    // Check if the coupon was applied
                    if ($quote->getCouponCode() != $couponCode) {
                        // When coupon not applied, reapply the previous one
                        if (!empty($previousCode)) {
                            // Attempt to set coupon code
                            $quote->setCouponCode($previousCode);

                            // Save cart
                            $this->cartRepository->save($quote);
                        }

                        $this->cartHelper->logError(
                            'SPC Coupon: CouponNotApplicable - The coupon '. $couponCode .
                            ' could not be applied to the cart. CartId: ' . $cartId . ' - ',
                            $cartDetails
                        );

                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase("The coupon code '". $couponCode ."' does not apply"),
                            "CouponNotApplicable",
                            400
                        );
                    }
                } else {
                    if (!isset($cartDetails['coupons'][0]['coupon_code'])
                        || $cartDetails['coupons'][0]['coupon_code'] === null) {
                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase("Coupon code is missing"),
                            "CouponNotApplicable",
                            400
                        );
                    }
                }
            }
        } else {
            throw new \Magento\Framework\Webapi\Exception(
                new Phrase("Cart details are missing on the request body"),
                "InvalidRequest",
                400
            );
        }

        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }
}
