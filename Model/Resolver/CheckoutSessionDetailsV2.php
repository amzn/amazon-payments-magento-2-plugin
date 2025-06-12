<?php

namespace Amazon\Pay\Model\Resolver;

use Amazon\Pay\Model\CheckoutSessionManagement;
use Amazon\Pay\Model\Resolver\CheckoutSessionDetails;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class CheckoutSessionDetailsV2 extends CheckoutSessionDetails implements ResolverInterface
{

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
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $amazonSessionId = $args['amazonSessionId'] ?? false;
        $queryTypes = ['shipping','billing','payment'];

        if (!$amazonSessionId) {
            throw new GraphQlInputException(__('Required parameter "amazonSessionId" is missing'));
        }

        if (!$queryTypes) {
            throw new GraphQlInputException(__('Required parameter "queryTypes" is missing'));
        }

        $response = [];
        foreach ($queryTypes as $queryType) {
            $response[$queryType] = $this->getQueryTypesData($amazonSessionId, $queryType) ?:  [];

            switch ($queryType) {
                case 'shipping':
                case 'billing':
                    $response[$queryType] = $response[$queryType][0];
                    break;
                default:
                    //nothing
            }
        }

        return $response;
    }
}
