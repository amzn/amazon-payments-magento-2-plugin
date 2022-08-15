<?php

namespace Amazon\Pay\Model\Spc;

use Amazon\Pay\Api\SpcCreateOrderInterface;
use Amazon\Pay\Model\Config\Source\AuthorizationMode;
use Amazon\Pay\Model\Config\Source\PaymentAction;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class CreateOrder implements SpcCreateOrderInterface
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
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function createOrder(int $cartId, string $checkoutSessionId)
    {
        // Get quote
        try {
            $quote = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            throw new Exception(
                new Phrase($e->getMessage())
            );
        }

        // Set payment method
        $quote->getPayment()->importData(['method' => 'checkmo']);

        // Set addresses' final details
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFirstname($shippingAddress->getFirstname() ?: 'First')
            ->setLastname($shippingAddress->getLastname() ?: 'Last')
            ->setSameAsBilling(true)
            ->setEmail($shippingAddress->getEmail() ?: 'test@example.com');
        $quote->setShippingAddress($shippingAddress);
        $quote->getBillingAddress()
            ->setEmail($shippingAddress->getEmail() ?: 'test@example.com')
            ->setFirstname($shippingAddress->getFirstname())
            ->setLastname($shippingAddress->getLastname())
            ->setStreet($shippingAddress->getStreet())
            ->setCity($shippingAddress->getCity())
            ->setRegion($shippingAddress->getRegion())
            ->setRegionId($shippingAddress->getRegionId())
            ->setCountryId($shippingAddress->getCountryId())
            ->setPostcode($shippingAddress->getPostcode())
            ->setTelephone($shippingAddress->getTelephone())
            ;
        $quote->setCustomerEmail($shippingAddress->getEmail() ?: 'test@example.com');

        // Collect totals
        $quote->collectTotals();

        $this->cartRepository->save($quote);

        // Place order
        $orderId = $this->cartManagement->placeOrder($quote->getId());
//        $order = $this->orderRepository->get($orderId);


        return [
            [
                'cartId' => $quote->getId(),
                'orderId' => $orderId,
            ]
        ];
    }
}
