<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CheckoutSessionSignIn implements ResolverInterface
{

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * CheckoutSessionSignIn constructor
     *
     * @param CheckoutSessionManagement $checkoutSessionManagement
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagement
    ) {
        $this->checkoutSessionManagement = $checkoutSessionManagement;
    }

    /**
     * Sign customer in through CheckoutSessionManagement if arguments were provided
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array[]
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $buyerToken = $args['buyerToken'] ?? false;

        if (!$buyerToken) {
            throw new GraphQlInputException(__('Required parameter "buyerToken" is missing'));
        }

        $response = $this->checkoutSessionManagement->signIn($buyerToken);
        return array_shift($response);
    }
}
