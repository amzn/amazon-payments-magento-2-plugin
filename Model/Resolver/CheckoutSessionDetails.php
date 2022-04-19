<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CheckoutSessionDetails implements ResolverInterface
{

    const QUERY_TYPES = ['billing', 'payment', 'shipping'];

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * @param CheckoutSessionManagement $checkoutSessionManagement
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagement
    ) {
        $this->checkoutSessionManagement = $checkoutSessionManagement;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $amazonSessionId = $args['amazonSessionId'] ?? false;
        $queryTypes = $args['queryTypes'] ?? false;

        if (!$amazonSessionId) {
            throw new GraphQlInputException(__('Required parameter "amazonSessionId" is missing'));
        }

        if (!$queryTypes) {
            throw new GraphQlInputException(__('Required parameter "queryTypes" is missing'));
        }

        $response = [];
        foreach ($queryTypes as $queryType) {
            $response[$queryType] = $this->getQueryTypesData($amazonSessionId, $queryType) ?:  [];
        }

        return [
            'response' => json_encode($response)
        ];
    }

    /**
     * @param $amazonSessionId
     * @param $queryType
     * @return void
     */
    private function getQueryTypesData($amazonSessionId, $queryType)
    {
        $result = false;
        if (in_array($queryType, self::QUERY_TYPES, true)) {
            switch ($queryType) {
                case 'billing':
                    $result = $this->checkoutSessionManagement->getBillingAddress($amazonSessionId);
                    break;
                case 'payment':
                    $result = $this->checkoutSessionManagement->getPaymentDescriptor($amazonSessionId);
                    break;
                case 'shipping':
                    $result = $this->checkoutSessionManagement->getShippingAddress($amazonSessionId);
            }
        }

        return $result;
    }
}
