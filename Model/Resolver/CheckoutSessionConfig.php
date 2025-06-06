<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CheckoutSessionConfig implements ResolverInterface
{

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * CheckoutSessionConfig constructor
     *
     * @param CheckoutSessionManagement $checkoutSessionManagement
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagement
    ) {
        $this->checkoutSessionManagement = $checkoutSessionManagement;
    }

    /**
     * Get config from CheckoutSessionManagement
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $cartId = $args['cartId'] ?? null;
        $omitPayloads = $args['omitPayloads'] ?? false;

        $response = $this->checkoutSessionManagement->getConfig($cartId, $omitPayloads);
        return array_shift($response);
    }
}
