<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SetCustomerLink implements ResolverInterface
{

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagementModel;

    /**
     * @param CheckoutSessionManagement $checkoutSessionManagementModel
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagementModel
    ){
        $this->checkoutSessionManagementModel = $checkoutSessionManagementModel;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array[]|Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $buyerToken = $args['buyerToken'] ?? false;
        $password = $args['password'] ?? false;

        if (!$buyerToken) {
            throw new GraphQlInputException(__('Required parameter "buyerToken" is missing'));
        }

        if (!$password) {
            throw new GraphQlInputException(__('Required parameter "password" is missing'));
        }

        return [
            'response' => array_merge(...$this->checkoutSessionManagementModel->setCustomerLink($buyerToken, $password))
        ];

    }
}
