<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Core\Model\Validation;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Core\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Framework\Validator\AbstractValidator;
use AmazonPay\ResponseInterface;

class ApiCredentialsValidator extends AbstractValidator
{
    const TEST_ORDER_REF = 'S00-0000000-0000000';

    /**
     * @var ClientFactoryInterface
     */
    private $amazonHttpClientFactory;

    /**
     * @var Data
     */
    private $amazonCoreHelper;

    /**
     * @param ClientFactoryInterface $amazonHttpClientFactory
     * @param Data                   $amazonCoreHelper
     */
    public function __construct(
        ClientFactoryInterface $amazonHttpClientFactory,
        Data $amazonCoreHelper
    ) {
        $this->amazonHttpClientFactory = $amazonHttpClientFactory;
        $this->amazonCoreHelper = $amazonCoreHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($scopeId = null, $scope = 'default')
    {
        if (empty($scopeId)) {
            $scopeId = null;
        }

        try {
            // convert to DataObject for an easier array key querying
            $response = new DataObject($this->sendTestRequest($scopeId, $scope)->toArray());
        } catch (\Exception $e) {
            $this->_addMessages(['An error occurred while connecting to Amazon: ' . $e->getMessage()]);
            return false;
        }

        if ($this->isValidResponse($response)) {
            $this->_addMessages(['Your Amazon configuration is valid.']);
            return true;
        } else {
            $this->_addMessages([$this->getErrorMessage($response)]);
            return false;
        }
    }

    /**
     * @param null|string $scopeId
     * @param null|string $scope
     *
     * @return ResponseInterface
     */
    protected function sendTestRequest($scopeId = null, $scope = 'default')
    {
        $client = $this->amazonHttpClientFactory->create($scopeId, $scope);
        return $client->getOrderReferenceDetails(['amazon_order_reference_id' => self::TEST_ORDER_REF]);
    }

    /**
     * @param DataObject $response
     *
     * @return bool
     */
    protected function isValidResponse(DataObject $response)
    {
        $isError = $response->getData('ResponseStatus') == '404';
        $isInvalidOrderRef = $response->getData('Error/Code') === 'InvalidOrderReferenceId';

        return $isError && $isInvalidOrderRef;
    }

    /**
     * @param DataObject $response
     *
     * @return string
     */
    protected function getErrorMessage(DataObject $response)
    {
        // special case for 200
        if ($response->getData('ResponseStatus') == '200') {
            return
                'Amazon responded with 200 on the OrderReference check. ' .
                'Although the configuration is correct, you are probably using a valid order reference' .
                ' to do the checking, and must revert to using S00-0000000-0000000 instead.'
            ;
        }

        $message = $response->getData('Error/Message') ?: 'There was an unknown error in the Amazon service.';

        return sprintf($message . ' (Error code: %s)', $response->getData('Error/Code') ?: 'Unknown error code');
    }
}
