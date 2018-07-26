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

namespace Amazon\Payment\Model\Adapter;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Payment\Domain\AmazonSetOrderDetailsResponseFactory;
use Magento\Payment\Model\Method\Logger;
use Amazon\Payment\Domain\AmazonAuthorizationResponseFactory;
use Amazon\Payment\Domain\AmazonCaptureResponseFactory;
use Amazon\Payment\Gateway\Helper\SubjectReader;
use Amazon\Core\Helper\Data;
use Amazon\Payment\Api\Data\PendingAuthorizationInterfaceFactory;
use Amazon\Payment\Api\Data\PendingCaptureInterfaceFactory;

/**
 * Class AmazonPaymentAdapter
 * Use \Amazon\Payment\Model\Adapter\AmazonPaymentAdapterFactory to create new instance of adapter.
 * @codeCoverageIgnore
 */
class AmazonPaymentAdapter
{
    const SUCCESS_CODES = ['Open', 'Closed', 'Completed'];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var AmazonSetOrderDetailsResponseFactory
     */
    private $amazonSetOrderDetailsResponseFactory;

    /**
     * @var AmazonCaptureResponseFactory
     */
    private $amazonCaptureResponseFactory;

    /**
     * @var AmazonAuthorizationResponseFactory
     */
    private $amazonAuthorizationResponseFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var PendingCaptureInterfaceFactory
     */
    private $pendingCaptureFactory;

    /**
     * @var PendingAuthorizationInterfaceFactory
     */
    private $pendingAuthorizationFactory;

    /**
     * AmazonPaymentAdapter constructor.
     * @param ClientFactoryInterface $clientFactory
     * @param AmazonCaptureResponseFactory $amazonCaptureResponseFactory
     * @param AmazonSetOrderDetailsResponseFactory $amazonSetOrderDetailsResponseFactory
     * @param AmazonAuthorizationResponseFactory $amazonAuthorizationResponseFactory
     * @param PendingCaptureInterfaceFactory $pendingCaptureFactory
     * @param PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory
     * @param SubjectReader $subjectReader
     * @param Data $coreHelper
     * @param Logger $logger
     */
    public function __construct(
        ClientFactoryInterface $clientFactory,
        AmazonCaptureResponseFactory $amazonCaptureResponseFactory,
        AmazonSetOrderDetailsResponseFactory $amazonSetOrderDetailsResponseFactory,
        AmazonAuthorizationResponseFactory $amazonAuthorizationResponseFactory,
        PendingCaptureInterfaceFactory $pendingCaptureFactory,
        PendingAuthorizationInterfaceFactory $pendingAuthorizationFactory,
        SubjectReader $subjectReader,
        Data $coreHelper,
        Logger $logger
    )
    {
        $this->clientFactory = $clientFactory;
        $this->amazonSetOrderDetailsResponseFactory = $amazonSetOrderDetailsResponseFactory;
        $this->logger = $logger;
        $this->amazonCaptureResponseFactory = $amazonCaptureResponseFactory;
        $this->amazonAuthorizationResponseFactory = $amazonAuthorizationResponseFactory;
        $this->subjectReader = $subjectReader;
        $this->coreHelper = $coreHelper;
        $this->pendingCaptureFactory = $pendingCaptureFactory;
        $this->pendingAuthorizationFactory = $pendingAuthorizationFactory;
    }

    /**
     * Sets Amazon Pay order data
     *
     * @param $storeId
     * @param $data
     * @return array
     */
    public function setOrderReferenceDetails($storeId, $data)
    {
        $response = [];

        try {
            $responseParser = $this->clientFactory->create($storeId)->setOrderReferenceDetails($data);
            $constraints = $this->amazonSetOrderDetailsResponseFactory->create(
                [
                    'response' => $responseParser
                ]
            );

            $response = [
                'status' => $responseParser->response['Status'],
                'constraints' => $constraints->getConstraints()
            ];
        } catch (\Exception $e) {
            $log['error'] = $e->getMessage();
            $this->logger->debug($log);
        }

        return $response;
    }

    /**
     * Confirms that payment has been created for Amazon Pay
     *
     * @param  $storeId
     * @param  $amazonOrderReferenceId
     * @return array
     */
    private function confirmOrderReference($storeId, $amazonOrderReferenceId)
    {
        $response = [];

        $response = $this->clientFactory->create($storeId)->confirmOrderReference(
            [
                'amazon_order_reference_id' => $amazonOrderReferenceId
            ]
        );

        if (!$response) {
            $log['error'] = __('Unable to confirm order reference.');
            $this->logger->debug($log);
        }

        return $response;
    }

    /**
     * @param $storeId
     * @param $data
     * @return \Amazon\Payment\Domain\AmazonAuthorizationResponse|\Amazon\Payment\Domain\Details\AmazonAuthorizationDetails
     */
    private function getAuthorization($storeId, $data)
    {
        $response = null;

        $client = $this->clientFactory->create($storeId);

        $responseParser = $client->authorize($data);
        $response = $this->amazonAuthorizationResponseFactory->create(['response' => $responseParser]);

        return $response ? $response->getDetails() : $response;
    }

    /**
     * @param $data
     * @param bool $captureNow
     * @return array
     */
    public function authorize($data, $captureNow = false, $attempts = 0)
    {
        $response = [];
        $confirmResponse = null;
        $storeId = $this->subjectReader->getStoreId();
        $authMode = $this->coreHelper->getAuthorizationMode('store', $storeId);

        (isset($data['additional_information']) && $data['additional_information'] != 'default')
            ? $additionalInformation = $data['additional_information'] : $additionalInformation = '';

        if ($additionalInformation) {
            if ($additionalInformation == 'TransactionTimedOut') {
                $response['response_code'] = 'TransactionTimedOut';
            }
            unset($data['additional_information']);
        }

        $authorizeData = [
            'amazon_order_reference_id' => $data['amazon_order_reference_id'],
            'authorization_amount' => $data['amount'],
            'currency_code' => $data['currency_code'],
            'authorization_reference_id' => $data['amazon_order_reference_id'] . '-A' . time().$attempts,
            'capture_now' => $captureNow,
            'transaction_timeout' => 0
        ];

        /** if first synchronous attempt failed, on second attempt try an asynchronous attempt. */
        if ($authMode != 'synchronous' && $attempts) {
            $authorizeData['transaction_timeout'] = 5;
        }

        $response['status'] = false;
        $response['attempts'] = $attempts;
        $response['auth_mode'] = $authMode;
        $response['constraints'] = [];
        $response['amazon_order_reference_id'] = $data['amazon_order_reference_id'];

        if (!$attempts) {
            $detailResponse = $this->setOrderReferenceDetails($storeId, $data);

            if (isset($detailResponse['constraints']) && !empty($detailResponse['constraints'])) {
                $response['constraints'] = $detailResponse['constraints'];
                return $response;
            }
        }

        $confirmResponse = $this->confirmOrderReference($storeId, $data['amazon_order_reference_id']);

        if ($confirmResponse->response['Status'] == 200) {

            $authorizeResponse = $this->getAuthorization($storeId, $authorizeData);

            if ($authorizeResponse) {
                if ($authorizeResponse->getCaptureTransactionId() || $authorizeResponse->getAuthorizeTransactionId()) {
                    $response['authorize_transaction_id'] = $authorizeResponse->getAuthorizeTransactionId();

                    if ($authorizeResponse->getStatus()->getState() == 'Pending' && $authMode == 'synchronous_possible') {
                        if ($captureNow) {
                            $response['capture_transaction_id'] = $authorizeResponse->getCaptureTransactionId();
                        }
                        $response['response_code'] = 'TransactionTimedOut';
                    } elseif (!in_array($authorizeResponse->getStatus()->getState(), self::SUCCESS_CODES)) {
                        $response['response_code'] = $authorizeResponse->getStatus()->getReasonCode();
                    } else {
                        $response['status'] = true;

                        if ($captureNow) {
                            $response['capture_transaction_id'] = $authorizeResponse->getCaptureTransactionId();
                        }
                    }
                } else {
                    $response['status'] = false;
                    $response['response_status'] = $authorizeResponse->getStatus()->getState();
                    $response['response_code'] = $authorizeResponse->getStatus()->getReasonCode();
                    $log['error'] = $authorizeResponse->getStatus()->getState() . ': ' . $authorizeResponse->getStatus()->getReasonCode();
                    $this->logger->debug($log);
                }
            }
        } else {
            /** something went wrong, parse response body for use by authorization validator */
            $response['response_status'] = $confirmResponse->response['Status'];

            $xml = simplexml_load_string($confirmResponse->response['ResponseBody']);
            $code = $xml->Error->Code[0];
            if ($code) {
                $response['response_code'] = (string)$code;
            } else {
                $log['error'] = __('AmazonPaymentAdapter: Improperly formatted XML response, no response code available.');
                $this->logger->debug($log);
            }
        }

        if ($additionalInformation) {
            $response['sandbox'] = $additionalInformation;
        }

        return $response;
    }

    /**
     * @param $data
     * @param $storeId
     * @return array
     */
    public function completeCapture($data, $storeId)
    {
        $response = [
            'status' => false
        ];

        $responseParser = $this->clientFactory->create($storeId)->capture($data);
        if ($responseParser->response['Status'] == 200) {
            $captureResponse = $this->amazonCaptureResponseFactory->create(['response' => $responseParser]);
            $capture = $captureResponse->getDetails();

            if (in_array($capture->getStatus()->getState(), self::SUCCESS_CODES)) {
                $response = [
                    'status' => true,
                    'transaction_id' => $capture->getTransactionId(),
                    'reauthorized' => false
                ];
            } elseif ($capture->getStatus()->getState() == 'Pending') {
                $order = $this->subjectReader->getOrder();

                try {
                    $this->pendingCaptureFactory->create()
                        ->setCaptureId($capture->getTransactionId())
                        ->setOrderId($order->getId())
                        ->setPaymentId($order->getPayment()->getEntityId())
                        ->save();
                } catch (\Exception $e) {
                    $log['error'] = __('AmazonPaymentAdapter: Unable to capture pending information for capture.');
                    $this->logger->debug($log);
                }
            } else {
                $response['response_code'] = $capture->getReasonCode();
            }
        } else {
            $log['error'] = __('AmazonPaymentAdapter: Bad status - no capture details available.');
            $this->logger->debug($log);
        }

        return $response;
    }

    /**
     * @param $data
     * @return bool
     */
    public function checkAuthorizationStatus($data)
    {

        $authorizeData = [
            'amazon_authorization_id' => $data['amazon_authorization_id']
        ];

        $storeId = $data['store_id'];

        $responseParser = $this->clientFactory->create($storeId)->getAuthorizationDetails($authorizeData);
        if ($responseParser->response['Status'] != 200) {
            $log['error'] = 'AmazonPaymentAdapter: Called getAuthorizationDetails and received bad status response: '
                . $responseParser->response['Status'];
            $this->logger->debug($log);
            return false;
        }

        return true;
    }
}
