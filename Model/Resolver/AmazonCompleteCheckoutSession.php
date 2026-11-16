<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Logger\ExceptionLogger;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManagerInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class AmazonCompleteCheckoutSession implements ResolverInterface
{
    /**
     * Available in Magento_SalesGraphQl from 2.4.x only, used to fill PlaceOrderOutput.orderV2 (2.4.7+)
     */
    private const ORDER_FORMATTER_CLASS = \Magento\SalesGraphQl\Model\Formatter\Order::class;

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagementModel;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ExceptionLogger
     */
    private $exceptionLogger;

    /**
     * AmazonCompleteCheckoutSession constructor
     *
     * @param CheckoutSessionManagement $checkoutSessionManagementModel
     * @param GetCartForUser $getCartForUser
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param ExceptionLogger $exceptionLogger
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagementModel,
        GetCartForUser $getCartForUser,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        ExceptionLogger $exceptionLogger
    ) {
        $this->checkoutSessionManagementModel = $checkoutSessionManagementModel;
        $this->getCartForUser = $getCartForUser;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->exceptionLogger = $exceptionLogger;
    }

    /**
     * Complete checkout session and return the placed order as a standard PlaceOrderOutput
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $cartId = $args['input']['cart_id'] ?? '';
        $amazonSessionId = $args['input']['amazon_session_id'] ?? '';

        if (!$cartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (!$amazonSessionId) {
            throw new GraphQlInputException(__('Required parameter "amazon_session_id" is missing'));
        }

        // Same cart ownership and state checks as the native placeOrder mutation
        $customerId = (int)$context->getUserId();
        $storeId = (int)$this->storeManager->getStore()->getId();
        $this->getCartForUser->execute($cartId, $customerId, $storeId);

        try {
            $result = $this->checkoutSessionManagementModel->completeCheckoutSession($amazonSessionId, $cartId);
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw new GraphQlInputException(__('Unable to complete Amazon Pay checkout.'));
        }

        if (empty($result['success']) || empty($result['order_id'])) {
            $message = $result['message'] ?? 'Unable to complete Amazon Pay checkout.';
            throw new GraphQlInputException(__($message));
        }

        $order = $this->orderRepository->get($result['order_id']);

        return [
            'order' => [
                'order_number' => $order->getIncrementId(),
                'order_id' => $order->getIncrementId(),
            ],
            'orderV2' => $this->formatOrder($order),
            'errors' => [],
        ];
    }

    /**
     * Format order for the CustomerOrder type when Magento_SalesGraphQl supports it
     *
     * @param OrderInterface $order
     * @return array|null
     */
    private function formatOrder(OrderInterface $order)
    {
        if (!class_exists(self::ORDER_FORMATTER_CLASS)) {
            return null;
        }

        return $this->objectManager->get(self::ORDER_FORMATTER_CLASS)->format($order);
    }
}
