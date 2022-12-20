<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\ShippingMethodInterface;
use Amazon\Pay\Helper\Spc\CheckoutSession;
use Amazon\Pay\Helper\Spc\ShippingMethod as ShippingMethodHelper;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Directory\Model\Currency;

class ShippingMethod implements ShippingMethodInterface
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
     * @var ShippingMethodHelper
     */
    protected $shippingMethodHelper;

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
     * @param ShippingMethodHelper $shippingMethodHelper
     * @param Currency $currency
     * @param CheckoutSession $checkoutSessionHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AmazonPayAdapter $amazonPayAdapter,
        Cart $cartHelper,
        ShippingMethodHelper $shippingMethodHelper,
        Currency $currency,
        CheckoutSession $checkoutSessionHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->cartHelper = $cartHelper;
        $this->shippingMethodHelper = $shippingMethodHelper;
        $this->currency = $currency;
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
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC ShippingMethod: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase('InvalidCartId'), "Cart Id ". $cartId ." not found or inactive", 404
            );
        }

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Check checkout session to
        if ($cartDetails && $checkoutSessionId) {
            $methodCode = $cartDetails['delivery_options'][0]['id'] ?? false;

            if (empty($methodCode)) {
                throw new \Magento\Framework\Webapi\Exception(
                    new Phrase('InvalidShippingMethod'), "Shipping method id missing", 400
                );
            }
            else {
                if ($this->checkoutSessionHelper->confirmCheckoutSession($quote, $cartDetails, $checkoutSessionId)) {
                    // Set the shipping method
                    $appliedMethod = $this->shippingMethodHelper->setShippingMethodOnQuote($quote, $methodCode);

                    if ($appliedMethod == ShippingMethodHelper::NOT_APPLIED) {
                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase('InvalidShippingMethod'), "Shipping method id was not able to apply to the cart", 400
                        );
                    }
                }
            }
        }

        // Construct response
        return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
    }
}
