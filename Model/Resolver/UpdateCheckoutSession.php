<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class UpdateCheckoutSession implements ResolverInterface
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
     * @return array|false[]|int
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $cartId = $args['cartId'] ?? false;
        $checkoutSessionId = $args['checkoutSessionId'] ?? false;

        if (!$cartId) {
            throw new GraphQlInputException(__('Required parameter "cartId" is missing'));
        }

        if (!$checkoutSessionId) {
            throw new GraphQlInputException(__('Required parameter "checkoutSessionId" is missing'));
        }

        $updateResponse = $this->checkoutSessionManagementModel->updateCheckoutSession($checkoutSessionId, $cartId);

        $redirectUrl = $updateResponse['webCheckoutDetails']['amazonPayRedirectUrl'] ?? false;
        if ($redirectUrl) {
            return [
                'redirectUrl' => $redirectUrl
            ];
        }

        // N/A would likely only be coalesced to if config isn't set. After running through some test cases will
        // update fallback messaging to be a bit more specific
        return [
            'redirectUrl' => $updateResponse['status'] ?? 'N/A'
        ];
    }
}
