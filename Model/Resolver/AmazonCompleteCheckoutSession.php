<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Logger\ExceptionLogger;
use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
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
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

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
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param ExceptionLogger $exceptionLogger
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagementModel,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        ExceptionLogger $exceptionLogger
    ) {
        $this->checkoutSessionManagementModel = $checkoutSessionManagementModel;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
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
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
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

        $this->validateCartForUser($cartId, (int)$context->getUserId());

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
     * Same cart ownership and state checks as the native placeOrder mutation (Magento_QuoteGraphQl GetCartForUser),
     * reimplemented to avoid a hard dependency on Magento_QuoteGraphQl
     *
     * Copy of the core logic from \Magento\QuoteGraphQl\Model\Cart\GetCartForUser::execute(), which may diverge
     * from future core versions. This method should be removed in the future in favor of the core GetCartForUser
     * once a dependency on Magento_QuoteGraphQl is acceptable for this module.
     *
     * @param string $cartId
     * @param int $customerId
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     */
    protected function validateCartForUser(string $cartId, int $customerId)
    {
        try {
            $cart = $this->cartRepository->get($this->maskedQuoteIdToQuoteId->execute($cartId));
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $cartId])
            );
        }

        if (!$cart->getIsActive()) {
            throw new GraphQlNoSuchEntityException(__('The cart isn\'t active.'));
        }

        if ((int)$cart->getStoreId() !== (int)$this->storeManager->getStore()->getId()) {
            throw new GraphQlNoSuchEntityException(
                __('Wrong store code specified for cart "%masked_cart_id"', ['masked_cart_id' => $cartId])
            );
        }

        if ((int)$cart->getCustomerId() !== $customerId) {
            throw new GraphQlAuthorizationException(
                __(
                    'The current user cannot perform operations on cart "%masked_cart_id"',
                    ['masked_cart_id' => $cartId]
                )
            );
        }
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
