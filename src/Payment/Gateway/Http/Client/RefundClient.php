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

namespace Amazon\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Payment\Domain\AmazonRefundResponseFactory;

/**
 * Class RefundClient
 * Amazon Pay refund client
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class RefundClient implements ClientInterface
{

    const SUCCESS_CODES = ['Open', 'Closed', 'Completed', 'Pending'];

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var AmazonRefundResponseFactory
     */
    private $refundResponseFactory;

    /**
     * RefundClient constructor.
     *
     * @param Logger                      $logger
     * @param ClientFactoryInterface      $clientFactory
     * @param AmazonRefundResponseFactory $refundResponseFactory
     */
    public function __construct(
        Logger $logger,
        ClientFactoryInterface $clientFactory,
        AmazonRefundResponseFactory $refundResponseFactory
    ) {
        $this->refundResponseFactory = $refundResponseFactory;
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {

        $data = $transferObject->getBody();

        $log = [
            'request' => $transferObject->getBody(),
            'client' => static::class
        ];

        $response = [];

        try {
            $response = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: "Something went wrong during Gateway request.");
            $log['error'] = $message;
            $this->logger->debug($log);
        } finally {
            $log['response'] = (array)$response;
            $this->logger->debug($log);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $store_id = $data['store_id'];
        unset($data['store_id']);

        $response = [
            'status' => false
        ];

        try {
            $client = $this->clientFactory->create($store_id);
            $responseParser = $client->refund($data);
            $refundResponse = $this->refundResponseFactory->create(['response' => $responseParser]);
            $refund = $refundResponse->getDetails();
        } catch (\Exception $e) {
            $log['error'] = $e->getMessage();
            $this->logger->debug($log);
        }

        $response['state'] = $refund->getRefundStatus()->getState();

        if (in_array($refund->getRefundStatus()->getState(), self::SUCCESS_CODES)) {
            $response['status'] = true;
            $response['refund_id'] = $refund->getRefundId();
        } else {
            $response['response_code'] = $refund->getRefundStatus()->getReasonCode();
        }

        // Gateway expects response to be in form of array
        return $response;
    }
}
