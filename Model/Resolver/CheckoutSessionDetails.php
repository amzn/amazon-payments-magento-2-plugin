<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @deprecated Replaced by CheckoutSessionDetailsV2
 * @see CheckoutSessionDetailsV2
 */
class CheckoutSessionDetails implements ResolverInterface
{

    public const QUERY_TYPES = ['billing', 'payment', 'shipping'];

    /**
     * @var CheckoutSessionManagement
     */
    private $checkoutSessionManagement;

    /**
     * CheckoutSessionDetails constructor
     *
     * @param CheckoutSessionManagement $checkoutSessionManagement
     */
    public function __construct(
        CheckoutSessionManagement $checkoutSessionManagement
    ) {
        $this->checkoutSessionManagement = $checkoutSessionManagement;
    }

    /**
     * Get one or more of paymentDescriptor, billingAddress, and shippingAddress from Amazon checkout session
     *
     * @param Field $field
     * @param ContextInterface $context
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
     * Get requested data from Amazon checkout session
     *
     * @param mixed $amazonSessionId
     * @param mixed $queryType
     * @return mixed
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
