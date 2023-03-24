<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CompleteCheckoutSession implements ResolverInterface
{

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagementModel;

    /**
     * CompleteCheckoutSession constructor
     *
     * @param CheckoutSessionManagement $checkoutSessionManagementModel
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagementModel
    ) {
        $this->checkoutSessionManagementModel = $checkoutSessionManagementModel;
    }

    /**
     * Complete checkout session through CheckoutSessionManagement if arguments were provided
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|false[]|int|Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $cartId = $args['cartId'] ?? false;
        $checkoutSessionId = $args['amazonSessionId'] ?? false;

        if (!$cartId) {
            throw new GraphQlInputException(__('Required parameter "cartId" is missing'));
        }

        if (!$checkoutSessionId) {
            throw new GraphQlInputException(__('Required parameter "checkoutSessionId" is missing'));
        }

        try {
            return $this->checkoutSessionManagementModel->completeCheckoutSession($checkoutSessionId, $cartId);
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }
    }
}
