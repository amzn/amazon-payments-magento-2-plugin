<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\Spc\OrderInterface;
use Amazon\Pay\Helper\Spc\Cart;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
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
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param AmazonPayAdapter $amazonPayAdapter
     * @param Cart $cartHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        AmazonPayAdapter $amazonPayAdapter,
        Cart $cartHelper,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->amazonPayAdapter = $amazonPayAdapter;
        $this->cartHelper = $cartHelper;
        $this->orderRepository = $orderRepository;
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
                    'SPC Order: '. $amazonSession['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['reasonCode'])
                );
            }

            if ($amazonSession['statusDetails']['state'] !== 'Open') {
                $this->cartHelper->logError(
                    'SPC Order: '. $amazonSession['statusDetails']['reasonCode'] .'. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new WebapiException(
                    new Phrase($amazonSession['statusDetails']['reasonCode'])
                );
            }

            // Check that the totals collect okay
            $quote->collectTotals();

            // Check that all items are still in stock
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$item->getProduct()->getExtensionAttributes()->getStockItem()->getIsInStock()) {
                    $this->cartHelper->logError(
                        'SPC Order: InvalidCartStatus - Product '. $item->getProduct()->getId() .' not in stock. CartId: '. $cartId .' - ', $cartDetails
                    );

                    throw new \Magento\Framework\Webapi\Exception(
                        new Phrase('InvalidCartStatus'), 422, 422
                    );
                }
            }

            // Check that both addresses are set
            if (is_array($quote->getShippingAddress()->validate()) || is_array($quote->getBillingAddress()->validate())) {
                $this->cartHelper->logError(
                    'SPC Order: InvalidCartStatus - Missing addresses. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new \Magento\Framework\Webapi\Exception(
                    new Phrase('InvalidCartStatus'), 422, 422
                );
            }

            // Check that the shipping method has been set
            if (empty($quote->getShippingAddress()->getShippingMethod())) {
                $this->cartHelper->logError(
                    'SPC Order: InvalidCartStatus - No shipping method selected. CartId: '. $cartId .' - ', $cartDetails
                );

                throw new \Magento\Framework\Webapi\Exception(
                    new Phrase('InvalidCartStatus'), 422, 422
                );
            }

            return $this->cartHelper->createResponse($quote->getId(), $checkoutSessionId);
        }
    }
}
