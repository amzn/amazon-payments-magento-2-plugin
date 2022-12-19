<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\CouponInterface;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
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
     * @param CartRepositoryInterface $cartRepository
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param Cart $cartHelper
     * @param Currency $currency
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonPayAdapter,
        Cart $cartHelper,
        Currency $currency
    )
    {
        $this->cartRepository = $cartRepository;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->cartHelper = $cartHelper;
        $this->currency = $currency;
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
                new Phrase('InvalidCartId'), 404, 404
            );
        }

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Get checkout session for verification
        if ($cartDetails && $checkoutSessionId) {
            $amazonSession = $this->amazonPayAdapter->getCheckoutSession($quote->getStoreId(), $checkoutSessionId);

            $amazonSessionStatus = $amazonSession['status'] ?? '404';
            if (!preg_match('/^2\d\d$/', $amazonSessionStatus)) {
                $this->cartHelper->logError(
                    'SPC Coupon: '. $amazonSession['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['reasonCode'])
                );
            }

            if ($amazonSession['statusDetails']['state'] !== 'Open') {
                $this->cartHelper->logError(
                    'SPC Coupon: '. $amazonSession['statusDetails']['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['statusDetails']['reasonCode'])
                );
            }

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
                        'SPC Coupon: CouponNotApplicable - The coupon could not be applied to the cart. CartId: '. $cartId .' - ', $cartDetails
                    );

                    throw new WebapiException(
                        new Phrase('CouponNotApplicable')
                    );
                }
            }
        }

        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }
}
