<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\OrderInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Helper\Spc\CheckoutSession;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Order implements OrderInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var AmazonPayAdapter
     */
    protected $amazonPayAdapter;

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
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param Cart $cartHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $checkoutSessionHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        AmazonPayAdapter $amazonPayAdapter,
        Cart $cartHelper,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSessionHelper
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->amazonPayAdapter = $amazonPayAdapter;
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
        } catch (NoSuchEntityException $e) {
            $this->cartHelper->logError('SPC Order: InvalidCartId. CartId: '. $cartId .' - ', $cartDetails);

            throw new \Magento\Framework\Webapi\Exception(
                new Phrase('InvalidCartId'), "Cart Id ". $cartId ." not found or inactive", 404
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
                            new Phrase('InvalidCartStatus'), "Item ". $item->getId() ." for product ". $item->getProduct()->getId() ." is out of stock", 422
                        );
                    }
                }

                // Check that both addresses are set
                if (is_array($quote->getShippingAddress()->validate()) || is_array($quote->getBillingAddress()->validate())) {
                    $this->cartHelper->logError(
                        'SPC Order: InvalidCartStatus - Missing addresses. CartId: ' . $cartId . ' - ', $cartDetails
                    );

                    throw new \Magento\Framework\Webapi\Exception(
                        new Phrase('InvalidCartStatus'), "Shipping and/or Billing Address is invalid", 422
                    );
                }

                // Check that the shipping method has been set
                if (empty($quote->getShippingAddress()->getShippingMethod())) {
                    $this->cartHelper->logError(
                        'SPC Order: InvalidCartStatus - No shipping method selected. CartId: ' . $cartId . ' - ', $cartDetails
                    );

                    throw new \Magento\Framework\Webapi\Exception(
                        new Phrase('InvalidCartStatus'), "No Shipping Method has been selected", 422
                    );
                }

                return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
            }
        }
    }
}
