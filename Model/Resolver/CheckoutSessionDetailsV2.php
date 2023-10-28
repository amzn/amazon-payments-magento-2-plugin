<?php

namespace Amazon\Pay\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Amazon\Pay\Model\CheckoutSessionManagement;

class CheckoutSessionDetailsV2 implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
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
     * @inheritDoc
     */
    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $amazonSessionId = $args['amazonSessionId'] ?? false;
        if (!$amazonSessionId) {
            throw new GraphQlInputException(__('Required parameter "amazonSessionId" is missing'));
        }

        $response = [];
        $fields = array_keys($info->getFieldSelection());
        foreach ($fields as $field) {
            $result = $this->getQueryTypesData($amazonSessionId, $field);
            if (!$result) {
                continue;
            }

            $response[$field] = $result;
        }

        if (empty($response)) {
            throw new GraphQlInputException(__('Amazon session not found.'));
        }

        return $response;
    }

    /**
     * @param string $amazonSessionId
     * @param string $queryType
     * @return mixed
     */
    protected function getQueryTypesData($amazonSessionId, $queryType)
    {
        switch ($queryType) {
            case 'billing':
                return $this->filterAddress($this->checkoutSessionManagement->getBillingAddress($amazonSessionId)[0] ?? null);
            case 'payment':
                return $this->checkoutSessionManagement->getPaymentDescriptor($amazonSessionId);
            case 'shipping':
                return $this->filterAddress($this->checkoutSessionManagement->getShippingAddress($amazonSessionId)[0] ?? null);
        }

        return null;
    }

    /**
     * @param array|null $address
     * @return array|null
     */
    protected function filterAddress(?array $address): ?array
    {
        if (!$address) {
            return $address;
        }

        /**
         * Remove empty street
         * ie: ['', 'Street2'] => ['Street2']
         */
        $street = $address['street'] ?? null;
        if (is_array($street)) {
            $address['street'] = array_filter($street);
        }

        return $address;
    }
}
