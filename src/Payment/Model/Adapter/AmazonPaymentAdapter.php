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
use Amazon\Payment\Model\OrderLinkFactory;
use Magento\Payment\Model\Method\Logger;
use Amazon\Payment\Domain\AmazonAuthorizationResponseFactory;
use Amazon\Payment\Domain\AmazonCaptureResponseFactory;
use Amazon\Payment\Gateway\Helper\SubjectReader;
use Amazon\Core\Helper\Data;
use Amazon\Payment\Api\Data\PendingAuthorizationInterfaceFactory;
use Amazon\Payment\Api\Data\PendingCaptureInterfaceFactory;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\App\ObjectManager;

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
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var OrderLinkFactory
     */
    private $orderLinkFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

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
     * @param UrlInterface $urlBuilder
     * @param OrderLinkFactory $orderLinkFactory
     * @param OrderRepository $orderRepository
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
        Logger $logger,
        UrlInterface $urlBuilder = null,
        OrderLinkFactory $orderLinkFactory = null,
        OrderRepository $orderRepository = null
    ) {
        $this->clientFactory = $clientFactory;
        $this->amazonSetOrderDetailsResponseFactory = $amazonSetOrderDetailsResponseFactory;
        $this->logger = $logger;
        $this->amazonCaptureResponseFactory = $amazonCaptureResponseFactory;
        $this->amazonAuthorizationResponseFactory = $amazonAuthorizationResponseFactory;
        $this->subjectReader = $subjectReader;
        $this->coreHelper = $coreHelper;
        $this->pendingCaptureFactory = $pendingCaptureFactory;
        $this->pendingAuthorizationFactory = $pendingAuthorizationFactory;
        $this->urlBuilder = $urlBuilder ?: ObjectManager::getInstance()->get(UrlInterface::class);
        $this->orderLinkFactory = $orderLinkFactory ?: ObjectManager::getInstance()->get(OrderLinkFactory::class);
        $this->orderRepository = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepository::class);
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
                'amazon_order_reference_id' => $amazonOrderReferenceId,
                'success_url' => $this->urlBuilder->getUrl('amazonpayments/payment/completecheckout'),
                'failure_url' => $this->urlBuilder->getUrl('amazonpayments/payment/completecheckout')
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
     * @param $amazonOrderReferenceId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getOrderByReference($amazonOrderReferenceId)
    {
        $orderLink = $this->orderLinkFactory->create()->load($amazonOrderReferenceId, 'amazon_order_reference_id');
        $orderId = $orderLink->getOrderId();
        if ($orderId === null) {
            return null;
        }
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param $data
     * @param bool $captureNow
     * @return array
     */
    public function authorize($data, $captureNow = false, $attempts = 0)
    {
        $response = [];
        $order = $this->getOrderByReference($data['amazon_order_reference_id']);
        if ($order) {
            $storeId = $order->getStoreId();
        } else {
            $storeId = $this->subjectReader->getStoreId();
        }
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

        if (isset($data['seller_authorization_note'])) {
            $authorizeData['seller_authorization_note'] = $data['seller_authorization_note'];
        }

        /** if first synchronous attempt failed, on second attempt try an asynchronous attempt. */
        if ($authMode != 'synchronous' && $attempts) {
            $authorizeData['transaction_timeout'] = 1440;
        }

        $response['status'] = false;
        $response['attempts'] = $attempts;
        $response['auth_mode'] = $authMode;
        $response['constraints'] = [];
        $response['amazon_order_reference_id'] = $data['amazon_order_reference_id'];

        $authorizeResponse = $this->getAuthorization($storeId, $authorizeData);

        if ($authorizeResponse->getCaptureTransactionId() || $authorizeResponse->getAuthorizeTransactionId()) {
            $response['authorize_transaction_id'] = $authorizeResponse->getAuthorizeTransactionId();

            if ($authorizeResponse->getStatus()->getState() == 'Pending' && $authMode == 'synchronous_possible') {
                if ($captureNow) {
                    $response['capture_transaction_id'] = $authorizeResponse->getCaptureTransactionId();
                }
                $response['response_code'] = 'TransactionTimedOut';
            } elseif (!in_array($authorizeResponse->getStatus()->getState(), self::SUCCESS_CODES)) {
                $response['response_code'] = $authorizeResponse->getStatus()->getReasonCode();
                if ($authMode == 'synchronous' && $authorizeResponse->getStatus()->getReasonCode() == 'TransactionTimedOut') {
                    $cancelData = [
                        'store_id' => $storeId,
                        'amazon_order_reference_id' => $data['amazon_order_reference_id']
                    ];
                    $this->clientFactory->create($storeId)->cancelOrderReference($cancelData);
                }
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
     * @param $storeId
     * @param $amazonId
     * @param $orderId
     */
    public function setOrderAttributes($storeId, $amazonId, $orderId)
    {
        $orderAttributes = [
            'amazon_order_reference_id' => $amazonId,
            'seller_order_id' => $orderId
        ];

        $this->clientFactory->create($storeId)->setOrderAttributes($orderAttributes);
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
