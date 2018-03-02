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
namespace Amazon\Payment\Model\Method;

use Amazon\Core\Client\ClientFactoryInterface;
use Amazon\Core\Exception\AmazonWebapiException;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Core\Model\Config\Source\AuthorizationMode;
use Amazon\Payment\Api\Data\QuoteLinkInterfaceFactory;
use Amazon\Payment\Api\OrderInformationManagementInterface;
use Amazon\Payment\Model\PaymentManagement;
use Amazon\Payment\Domain\AmazonAuthorizationDetailsResponseFactory;
use Amazon\Payment\Domain\AmazonAuthorizationResponseFactory;
use Amazon\Payment\Domain\AmazonAuthorizationStatus;
use Amazon\Payment\Domain\AmazonCaptureResponseFactory;
use Amazon\Payment\Domain\AmazonRefundResponseFactory;
use Amazon\Payment\Domain\Details\AmazonAuthorizationDetails;
use Amazon\Payment\Domain\Validator\AmazonAuthorization;
use Amazon\Payment\Domain\Validator\AmazonCapture;
use Amazon\Payment\Domain\Validator\AmazonPreCapture;
use Amazon\Payment\Domain\Validator\AmazonRefund;
use Amazon\Payment\Exception\AuthorizationExpiredException;
use Amazon\Payment\Exception\CapturePendingException;
use Amazon\Payment\Exception\HardDeclineException;
use Amazon\Payment\Exception\SoftDeclineException;
use Amazon\Payment\Exception\TransactionTimeoutException;
use Exception;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Amazon extends AbstractMethod
{
    const PAYMENT_METHOD_CODE = 'amazon_payment';
    const KEY_SANDBOX_SIMULATION_REFERENCE = 'sandbox_simulation_reference';

    /**
     * {@inheritdoc}
     */
    protected $_isGateway = true;

    /**
     * {@inheritdoc}
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /**
     * {@inheritdoc}
     */
    protected $_canCapture = true;

    /**
     * {@inheritdoc}
     */
    protected $_canAuthorize = true;

    /**
     * {@inheritdoc}
     */
    protected $_canRefund = true;

    /**
     * {@inheritdoc}
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * {@inheritdoc}
     */
    protected $_canUseInternal = false;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var QuoteLinkInterfaceFactory
     */
    private $quoteLinkFactory;

    /**
     * @var OrderInformationManagementInterface
     */
    private $orderInformationManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AmazonAuthorizationResponseFactory
     */
    private $amazonAuthorizationResponseFactory;

    /**
     * @var AmazonRefundResponseFactory
     */
    private $amazonRefundResponseFactory;

    /**
     * @var AmazonCaptureResponseFactory
     */
    private $amazonCaptureResponseFactory;

    /**
     * @var AmazonAuthorization
     */
    private $amazonAuthorizationValidator;

    /**
     * @var AmazonCapture
     */
    private $amazonCaptureValidator;

    /**
     * @var AmazonRefund
     */
    private $amazonRefundValidator;

    /**
     * @var PaymentManagement
     */
    private $paymentManagement;

    /**
     * @var AmazonPreCapture
     */
    private $amazonPreCaptureValidator;

    /**
     * @var AmazonAuthorizationDetailsResponseFactory
     */
    private $amazonAuthorizationDetailsResponseFactory;

    /**
     * @var AmazonCoreHelper
     */
    private $amazonCoreHelper;

    /**
     * @var integer
     */
    private $lastTransactionTime = 0;

    /**
     * Amazon constructor.
     *
     * @param Context                                   $context
     * @param Registry                                  $registry
     * @param ExtensionAttributesFactory                $extensionFactory
     * @param AttributeValueFactory                     $customAttributeFactory
     * @param Data                                      $paymentData
     * @param ScopeConfigInterface                      $scopeConfig
     * @param Logger                                    $logger
     * @param ClientFactoryInterface                    $clientFactory
     * @param QuoteLinkInterfaceFactory                 $quoteLinkFactory
     * @param OrderInformationManagementInterface       $orderInformationManagement
     * @param CartRepositoryInterface                   $cartRepository
     * @param AmazonAuthorizationResponseFactory        $amazonAuthorizationResponseFactory
     * @param AmazonCaptureResponseFactory              $amazonCaptureResponseFactory
     * @param AmazonRefundResponseFactory               $amazonRefundResponseFactory
     * @param AmazonAuthorizationDetailsResponseFactory $amazonAuthorizationDetailsResponseFactory
     * @param AmazonAuthorization                       $amazonAuthorizationValidator
     * @param AmazonPreCapture                          $amazonPreCaptureValidator
     * @param AmazonCapture                             $amazonCaptureValidator
     * @param AmazonRefund                              $amazonRefundValidator
     * @param PaymentManagement                         $paymentManagement
     * @param AmazonCoreHelper                          $amazonCoreHelper
     * @param AbstractResource|null                     $resource
     * @param AbstractDb|null                           $resourceCollection
     * @param array                                     $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ClientFactoryInterface $clientFactory,
        QuoteLinkInterfaceFactory $quoteLinkFactory,
        OrderInformationManagementInterface $orderInformationManagement,
        CartRepositoryInterface $cartRepository,
        AmazonAuthorizationResponseFactory $amazonAuthorizationResponseFactory,
        AmazonCaptureResponseFactory $amazonCaptureResponseFactory,
        AmazonRefundResponseFactory $amazonRefundResponseFactory,
        AmazonAuthorizationDetailsResponseFactory $amazonAuthorizationDetailsResponseFactory,
        AmazonAuthorization $amazonAuthorizationValidator,
        AmazonPreCapture $amazonPreCaptureValidator,
        AmazonCapture $amazonCaptureValidator,
        AmazonRefund $amazonRefundValidator,
        PaymentManagement $paymentManagement,
        AmazonCoreHelper $amazonCoreHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->clientFactory                             = $clientFactory;
        $this->quoteLinkFactory                          = $quoteLinkFactory;
        $this->orderInformationManagement                = $orderInformationManagement;
        $this->cartRepository                            = $cartRepository;
        $this->amazonAuthorizationResponseFactory        = $amazonAuthorizationResponseFactory;
        $this->amazonCaptureResponseFactory              = $amazonCaptureResponseFactory;
        $this->amazonRefundResponseFactory               = $amazonRefundResponseFactory;
        $this->amazonAuthorizationValidator              = $amazonAuthorizationValidator;
        $this->amazonCaptureValidator                    = $amazonCaptureValidator;
        $this->amazonRefundValidator                     = $amazonRefundValidator;
        $this->paymentManagement                         = $paymentManagement;
        $this->amazonPreCaptureValidator                 = $amazonPreCaptureValidator;
        $this->amazonAuthorizationDetailsResponseFactory = $amazonAuthorizationDetailsResponseFactory;
        $this->amazonCoreHelper                          = $amazonCoreHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $this->authorizeInStore($payment, $amount, false);
    }

    public function authorizeInCron(InfoInterface $payment, $amount, $capture)
    {
        $amazonOrderReferenceId = $this->getAmazonOrderReferenceId($payment);
        $storeId                = $payment->getOrder()->getStoreId();
        $async                  = false;

        $this->_authorize($payment, $amount, $amazonOrderReferenceId, $storeId, $capture, $async);
    }

    /**
     * {@inheritdoc}
     */
    public function capture(InfoInterface $payment, $amount)
    {
        if ($payment->getParentTransactionId()) {
            $this->_capture($payment, $amount);
        } else {
            $this->authorizeInStore($payment, $amount, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $amazonOrderReferenceId = $this->getAmazonOrderReferenceId($payment);
        $captureId              = $payment->getParentTransactionId();
        $storeId                = $payment->getOrder()->getStoreId();

        $data = [
            'amazon_capture_id'   => $captureId,
            'refund_reference_id' => $amazonOrderReferenceId . '-R' . $this->getUniqueTransactionPostfix(),
            'refund_amount'       => $amount,
            'currency_code'       => $this->getCurrencyCode($payment)
        ];

        $transport = new DataObject($data);
        $this->_eventManager->dispatch(
            'amazon_payment_refund_before',
            [
                'context'   => 'refund',
                'payment'   => $payment,
                'transport' => $transport
            ]
        );
        $data = $transport->getData();

        $client = $this->clientFactory->create($storeId);

        $responseParser = $client->refund($data);
        $response       = $this->amazonRefundResponseFactory->create(['response' => $responseParser]);
        $refundDetails  = $response->getDetails();
        $this->amazonRefundValidator->validate($refundDetails);

        $payment->setTransactionId($refundDetails->getRefundId());

        $this->paymentManagement->queuePendingRefund($refundDetails, $payment);
    }

    protected function authorizeInStore(InfoInterface $payment, $amount, $capture = false)
    {
        $amazonOrderReferenceId = $this->getAmazonOrderReferenceId($payment);
        $storeId                = $payment->getOrder()->getStoreId();
        $authMode               = $this->amazonCoreHelper->getAuthorizationMode(ScopeInterface::SCOPE_STORE, $storeId);
        $async                  = (AuthorizationMode::ASYNC === $authMode);

        try {
            try {
                $this->_authorize($payment, $amount, $amazonOrderReferenceId, $storeId, $capture, $async);
            } catch (TransactionTimeoutException $e) {
                if (AuthorizationMode::SYNC_THEN_ASYNC === $authMode) {
                    $async = true;
                    $this->_authorize($payment, $amount, $amazonOrderReferenceId, $storeId, $capture, $async);
                } else {
                    throw $e;
                }
            }
        } catch (SoftDeclineException $e) {
            $this->processSoftDecline();
        } catch (Exception $e) {
            if (! $e instanceof HardDeclineException) {
                $this->_logger->error($e);
            }
            $this->processHardDecline($payment, $amazonOrderReferenceId);
        }
    }

    protected function reauthorizeAndCapture(
        InfoInterface $payment,
        $amount,
        $amazonOrderReferenceId,
        $authorizationId,
        $storeId
    ) {
        $this->paymentManagement->closeTransaction($authorizationId, $payment, $payment->getOrder());
        $payment->setParentTransactionId(null);
        $this->_authorize($payment, $amount, $amazonOrderReferenceId, $storeId, true);
    }

    protected function _authorize(
        InfoInterface $payment,
        $amount,
        $amazonOrderReferenceId,
        $storeId,
        $capture = false,
        $async = false
    ) {
        $data = [
            'amazon_order_reference_id'  => $amazonOrderReferenceId,
            'authorization_amount'       => $amount,
            'currency_code'              => $this->getCurrencyCode($payment),
            'authorization_reference_id' => $amazonOrderReferenceId . '-A' . $this->getUniqueTransactionPostfix(),
            'capture_now'                => $capture,
        ];

        if (! $async) {
            $data['transaction_timeout'] = 0;
        }

        $transport = new DataObject($data);
        $this->_eventManager->dispatch(
            'amazon_payment_authorize_before',
            [
                'context'   => ($capture) ? 'authorization_capture' : 'authorization',
                'payment'   => $payment,
                'transport' => $transport
            ]
        );
        $data = $transport->getData();

        $client = $this->clientFactory->create($storeId);

        $responseParser       = $client->authorize($data);
        $response             = $this->amazonAuthorizationResponseFactory->create(['response' => $responseParser]);
        $authorizationDetails = $response->getDetails();

        $this->amazonAuthorizationValidator->validate($authorizationDetails);

        $this->setAuthorizeTransaction($payment, $authorizationDetails, $capture);
    }

    protected function setAuthorizeTransaction(
        InfoInterface $payment,
        AmazonAuthorizationDetails $details,
        $capture
    ) {
        $pending       = (AmazonAuthorizationStatus::STATE_PENDING == $details->getStatus()->getState());
        $transactionId = $details->getAuthorizeTransactionId();

        $payment->setIsTransactionPending($pending);
        $payment->setIsTransactionClosed(false);

        if ($capture) {
            $transactionId = $details->getCaptureTransactionId();

            if (! $pending) {
                $payment->setIsTransactionClosed(true);
            }
        }

        if ($pending) {
            $this->paymentManagement->queuePendingAuthorization(
                $details,
                $payment->getOrder()
            );
        }

        $payment->setTransactionId($transactionId);
    }

    protected function processHardDecline(InfoInterface $payment, $amazonOrderReferenceId)
    {
        $storeId = $payment->getOrder()->getStoreId();

        $this->cancelOrderReference($amazonOrderReferenceId, $storeId);
        $this->deleteAmazonOrderReferenceId($payment);
        $this->reserveNewOrderId($payment);

        throw new AmazonWebapiException(
            __(
                'Unfortunately it is not possible to pay with Amazon Pay for this order. ' .
                'Please choose another payment method.'
            ),
            AmazonAuthorizationStatus::CODE_HARD_DECLINE,
            AmazonWebapiException::HTTP_FORBIDDEN
        );
    }

    protected function cancelOrderReference($amazonOrderReferenceId, $storeId)
    {
        try {
            $this->orderInformationManagement->cancelOrderReference($amazonOrderReferenceId, $storeId);
        } catch (Exception $e) {
            //ignored as it's likely in a cancelled state already or there is a problem we cannot rectify
            return;
        }
    }

    protected function processSoftDecline()
    {
        throw new AmazonWebapiException(
            __(
                'There has been a problem with the selected payment method on your Amazon account. ' .
                'Please choose another one.'
            ),
            AmazonAuthorizationStatus::CODE_SOFT_DECLINE,
            AmazonWebapiException::HTTP_FORBIDDEN
        );
    }

    protected function _capture(InfoInterface $payment, $amount)
    {
        $amazonOrderReferenceId = $this->getAmazonOrderReferenceId($payment);
        $authorizationId        = $payment->getParentTransactionId();
        $storeId                = $payment->getOrder()->getStoreId();

        if ($this->validatePreCapture($payment, $amount, $amazonOrderReferenceId, $authorizationId, $storeId)) {
            $data = [
                'amazon_authorization_id' => $authorizationId,
                'capture_amount'          => $amount,
                'currency_code'           => $this->getCurrencyCode($payment),
                'capture_reference_id'    => $amazonOrderReferenceId . '-C' . $this->getUniqueTransactionPostfix()
            ];

            $transport = new DataObject($data);
            $this->_eventManager->dispatch(
                'amazon_payment_capture_before',
                ['context' => 'capture', 'payment' => $payment, 'transport' => $transport]
            );
            $data = $transport->getData();

            $client = $this->clientFactory->create($storeId);

            try {
                $responseParser = $client->capture($data);
                $response       = $this->amazonCaptureResponseFactory->create(['response' => $responseParser]);
                $captureDetails = $response->getDetails();

                $this->amazonCaptureValidator->validate($captureDetails);
            } catch (CapturePendingException $e) {
                $payment->setIsTransactionPending(true);
                $payment->setIsTransactionClosed(false);

                if (isset($captureDetails)) {
                    $this->paymentManagement->queuePendingCapture(
                        $captureDetails,
                        $payment->getId(),
                        $payment->getOrder()->getId()
                    );
                }
            } finally {
                if (isset($captureDetails)) {
                    $payment->setTransactionId($captureDetails->getTransactionId());
                }
            }
        }
    }

    protected function validatePreCapture(
        InfoInterface $payment,
        $amount,
        $amazonOrderReferenceId,
        $authorizationId,
        $storeId
    ) {
        try {
            $data = [
                'amazon_authorization_id' => $authorizationId,
            ];

            $client = $this->clientFactory->create($storeId);

            $responseParser = $client->getAuthorizationDetails($data);
            $response       = $this->amazonAuthorizationDetailsResponseFactory->create(['response' => $responseParser]);

            $authorizationDetails = $response->getDetails();
            $this->amazonPreCaptureValidator->validate($authorizationDetails);

            return true;
        } catch (AuthorizationExpiredException $e) {
            $this->reauthorizeAndCapture($payment, $amount, $amazonOrderReferenceId, $authorizationId, $storeId);
        }

        return false;
    }

    protected function getCurrencyCode(InfoInterface $payment)
    {
        return $payment->getOrder()->getOrderCurrencyCode();
    }

    protected function getAmazonOrderReferenceId(InfoInterface $payment)
    {
        return $this->getQuoteLink($payment)->getAmazonOrderReferenceId();
    }

    protected function deleteAmazonOrderReferenceId(InfoInterface $payment)
    {
        $this->getQuoteLink($payment)->delete();
    }

    protected function reserveNewOrderId(InfoInterface $payment)
    {
        $this->getQuote($payment)
            ->setReservedOrderId(null)
            ->reserveOrderId()
            ->save();
    }

    protected function getQuote(InfoInterface $payment)
    {
        $quoteId = $payment->getOrder()->getQuoteId();
        return $this->cartRepository->get($quoteId);
    }

    protected function getQuoteLink(InfoInterface $payment)
    {
        $quoteId   = $payment->getOrder()->getQuoteId();
        $quoteLink = $this->quoteLinkFactory->create();
        $quoteLink->load($quoteId, 'quote_id');

        return $quoteLink;
    }

    protected function getUniqueTransactionPostfix()
    {
        $transactionTime = time();

        if ($this->lastTransactionTime === $transactionTime) {
            $transactionTime++;
        }

        $this->lastTransactionTime = $transactionTime;

        return $transactionTime;
    }

    /**
     * {@inheritdoc}
     */
    public function assignData(DataObject $data)
    {
        $additionalData = $data->getAdditionalData();

        if (! is_array($additionalData)) {
            return $this;
        }

        $additionalData = new DataObject($additionalData);

        $infoInstance = $this->getInfoInstance();
        $key          = self::KEY_SANDBOX_SIMULATION_REFERENCE;
        $infoInstance->setAdditionalInformation($key, $additionalData->getData($key));

        return $this;
    }
}
