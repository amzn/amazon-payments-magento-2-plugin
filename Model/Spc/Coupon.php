<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\CouponInterface;
use Amazon\Pay\Helper\Spc\CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Magento\SalesRule\Api\Data\CouponInterface as SalesRuleCouponInterface;
use Magento\Store\Api\Data\StoreInterface;

class Coupon implements CouponInterface
{
    const NOT_APPLICABLE = 'CouponNotApplicable';

    const INVALID = 'InvalidCouponCode';

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
     * @var SalesRuleCouponInterface
     */
    protected $salesRuleCoupon;


    /**
     * @param StoreInterface $store
     * @param CartRepositoryInterface $cartRepository
     * @param Cart $cartHelper
     * @param CheckoutSession $checkoutSessionHelper
     * @param SalesRuleCouponInterface $salesRuleCoupon
     */
    public function __construct(
        StoreInterface $store,
        CartRepositoryInterface $cartRepository,
        Cart $cartHelper,
        CheckoutSession $checkoutSessionHelper,
        SalesRuleCouponInterface $salesRuleCoupon
    )
    {
        $this->store = $store;
        $this->cartRepository = $cartRepository;
        $this->cartHelper = $cartHelper;
        $this->checkoutSessionHelper = $checkoutSessionHelper;
        $this->salesRuleCoupon = $salesRuleCoupon;
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
                new Phrase("Cart Id ". $cartId ." not found or inactive"), "InvalidCartId", 404
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

                    // Check if coupon exists
                    if (!$this->couponExists($couponCode)) {
                        $this->cartHelper->logError(
                            'SPC Coupon: '. self::INVALID .' - The coupon '. $couponCode .' is invalid. CartId: ' . $cartId . ' - ', $cartDetails
                        );

                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase("The coupon code '". $couponCode ."' is invalid"), self::INVALID, 400
                        );
                    }

                    // Empty out the quote items' rule ids, because Magento does not
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
                            'SPC Coupon: '. self::NOT_APPLICABLE .' - The coupon '. $couponCode .' could not be applied to the cart. CartId: ' . $cartId . ' - ', $cartDetails
                        );

                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase("The coupon code '". $couponCode ."' does not apply"), self::NOT_APPLICABLE, 400
                        );
                    }
                }
                else {
                    if (!isset($cartDetails['coupons'][0]['coupon_code']) || $cartDetails['coupons'][0]['coupon_code'] === null) {
                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase("Coupon code is missing"), self::NOT_APPLICABLE, 400
                        );
                    }
                }
            }
        }
        else {
            throw new \Magento\Framework\Webapi\Exception(
                new Phrase("Cart details are missing on the request body"), "InvalidRequest", 400
            );
        }

        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }

    /**
     * @param $couponCode
     * @return bool
     */
    protected function couponExists($couponCode)
    {
        try {
            $coupon = $this->salesRuleCoupon->loadByCode($couponCode);

            if ($coupon->getRuleId()) {
                return true;
            }

        }
        catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
