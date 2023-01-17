<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\ShippingMethodInterface;
use Amazon\Pay\Helper\Spc\CheckoutSession;
use Amazon\Pay\Helper\Spc\ShippingMethod as ShippingMethodHelper;
use Amazon\Pay\Helper\Spc\Cart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;

class ShippingMethod implements ShippingMethodInterface
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
     * @var ShippingMethodHelper
     */
    protected $shippingMethodHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSessionHelper;

    /**
     * @param StoreInterface $store
     * @param CartRepositoryInterface $cartRepository
     * @param Cart $cartHelper
     * @param ShippingMethodHelper $shippingMethodHelper
     * @param CheckoutSession $checkoutSessionHelper
     */
    public function __construct(
        StoreInterface $store,
        CartRepositoryInterface $cartRepository,
        Cart $cartHelper,
        ShippingMethodHelper $shippingMethodHelper,
        CheckoutSession $checkoutSessionHelper
    )
    {
        $this->store = $store;
        $this->cartRepository = $cartRepository;
        $this->cartHelper = $cartHelper;
        $this->shippingMethodHelper = $shippingMethodHelper;
        $this->checkoutSessionHelper = $checkoutSessionHelper;
    }

    /**
     * @inheritdoc
     */
    public function shippingMethod(int $cartId, $cartDetails = null)
    {
        // Get quote
        try {
            /** @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->cartRepository->getActive($cartId);

            // Set currency on the http context
            $this->store->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC ShippingMethod: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase("Cart Id ". $cartId ." not found or inactive"), "InvalidCartId", 404
            );
        }

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Check checkout session to
        if ($cartDetails && $checkoutSessionId) {
            $methodCode = $cartDetails['delivery_options'][0]['id'] ?? false;

            if (empty($methodCode)) {
                throw new \Magento\Framework\Webapi\Exception(
                    new Phrase("Shipping Method id missing"), "InvalidShippingMethod", 400
                );
            }
            else {
                if ($this->checkoutSessionHelper->confirmCheckoutSession($quote, $cartDetails, $checkoutSessionId)) {
                    // Set the shipping method
                    $appliedMethod = $this->shippingMethodHelper->setShippingMethodOnQuote($quote, $methodCode);

                    if ($appliedMethod == ShippingMethodHelper::NOT_APPLIED) {
                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase("Shipping method id '". $methodCode ."' was not able to apply to the cart"), "InvalidShippingMethod", 400
                        );
                    }
                }
            }
        }

        // Construct response
        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }
}
