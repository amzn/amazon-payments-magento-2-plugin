<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\OrderInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Helper\Spc\CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;

class Order implements OrderInterface
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
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSessionHelper;

    /**
     * @param StoreInterface $store
     * @param CartRepositoryInterface $cartRepository
     * @param Cart $cartHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $checkoutSessionHelper
     */
    public function __construct(
        StoreInterface $store,
        CartRepositoryInterface $cartRepository,
        Cart $cartHelper,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSessionHelper
    )
    {
        $this->store = $store;
        $this->cartRepository = $cartRepository;
        $this->cartHelper = $cartHelper;
        $this->orderRepository = $orderRepository;
        $this->checkoutSessionHelper = $checkoutSessionHelper;
    }

    /**
     * @inheritdoc
     */
    public function createOrder(int $cartId, $cartDetails = null)
    {
        // Get quote
        try {
            /** @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->cartRepository->getActive($cartId);

            // Set currency on the http context
            $this->store->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC Order: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase("Cart Id ". $cartId ." not found or inactive"), "InvalidCartId", 404
            );
        }

        // Get checkoutSessionId
        $checkoutSessionId = $cartDetails['checkout_session_id'] ?? null;

        // Get checkout session for verification
        if ($cartDetails && $checkoutSessionId) {
            if ($this->checkoutSessionHelper->confirmCheckoutSession($quote, $cartDetails, $checkoutSessionId)) {
                // Check that the totals collect okay
                $quote->collectTotals();

                // Check that all items are still in stock
                foreach ($quote->getAllVisibleItems() as $item) {
                    if (!$item->getProduct()->getExtensionAttributes()->getStockItem()->getIsInStock()) {
                        $this->cartHelper->logError(
                            'SPC Order: InvalidCartStatus - Product ' . $item->getProduct()->getId() . ' not in stock. CartId: ' . $cartId . ' - ', $cartDetails
                        );

                        throw new \Magento\Framework\Webapi\Exception(
                            new Phrase("Item ". $item->getId() ." for product ". $item->getProduct()->getId() ." is out of stock"), "InvalidCartStatus", 422
                        );
                    }
                }

                // Check that both addresses are set
                if (is_array($quote->getShippingAddress()->validate()) || is_array($quote->getBillingAddress()->validate())) {
                    $this->cartHelper->logError(
                        'SPC Order: InvalidCartStatus - Missing addresses. CartId: ' . $cartId . ' - ', $cartDetails
                    );

                    throw new \Magento\Framework\Webapi\Exception(
                        new Phrase("Shipping and/or Billing Address is invalid"), "InvalidCartStatus", 422
                    );
                }

                // Check that the shipping method has been set
                if (empty($quote->getShippingAddress()->getShippingMethod())) {
                    $this->cartHelper->logError(
                        'SPC Order: InvalidCartStatus - No shipping method selected. CartId: ' . $cartId . ' - ', $cartDetails
                    );

                    throw new \Magento\Framework\Webapi\Exception(
                        new Phrase("No Shipping Method set on cart"), "InvalidCartStatus", 422
                    );
                }

                return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
            }
        }
        else {
            throw new \Magento\Framework\Webapi\Exception(
                new Phrase("Cart details are missing on the request body"), "InvalidRequest", 400
            );
        }
    }
}
