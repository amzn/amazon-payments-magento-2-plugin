<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Amazon\Pay\Gateway\Response;

use Amazon\Pay\Gateway\Helper\SubjectReader;
use Amazon\Pay\Model\AsyncManagement;
use Amazon\Pay\Model\Config\Source\AuthorizationMode;
use Amazon\Pay\Model\Config\Source\PaymentAction;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Event\ManagerInterface;
use Amazon\Pay\Model\AsyncManagement\Charge as AsyncCharge;
use Amazon\Pay\Model\AmazonConfig as AmazonConfig;
use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use Magento\Quote\Api\CartRepositoryInterface;

class AuthorizationSaleVaultHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var AsyncManagement
     */
    private $asyncManagement;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var AsyncManagement\Charge
     */
    private $asyncCharge;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var Adapter\AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    private $quoteRepository;


    /**
     * AuthorizationHandler constructor.
     * @param SubjectReader $subjectReader
     * @param AsyncManagement $asyncManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param Magento\Framework\Event\ManagerInterface $eventManager
     * @param Adapter\AmazonPayAdapter $amazonAdapter
     * @param Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Amazon\Pay\Model\AsyncManagement\Charge $asyncCharge
     * @param Amazon\Pay\Model\AmazonConfig $amazonConfig
     */
    public function __construct(
        SubjectReader $subjectReader,
        AsyncManagement $asyncManagement,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $eventManager,
        AmazonPayAdapter $amazonAdapter,
        CartRepositoryInterface $quoteRepository,
        AsyncCharge $asyncCharge,
        AmazonConfig $amazonConfig

    ) {
        $this->subjectReader = $subjectReader;
        $this->asyncManagement = $asyncManagement;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->amazonAdapter = $amazonAdapter;
        $this->quoteRepository = $quoteRepository;
        $this->asyncCharge = $asyncCharge;
        $this->amazonConfig = $amazonConfig;

    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var Payment $payment */
            $payment = $paymentDO->getPayment();
            $order = $payment->getOrder();
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);

            $transactionId = $response['chargeId'];;
            $payment->setTransactionId($transactionId);
            
            $chargeState = $response['statusDetails']['state'];
            if ($chargeState != 'Captured' && 
                $this->amazonConfig->getPaymentAction() == PaymentAction::AUTHORIZE_AND_CAPTURE) {
                
                // capture on Amazon Pay
                $this->amazonAdapter->captureCharge(
                    $quote->getStoreId(),
                    $transactionId,
                    $quote->getGrandTotal(),
                    $quote->getQuoteCurrencyCode()
                );
            }

            $amazonCharge = $this->amazonAdapter->getCharge($quote->getStoreId(), $transactionId);

            switch ($chargeState) {
                case 'AuthorizationInitiated':
                    $payment->setIsTransactionPending(true);
                    $payment->setIsTransactionClosed(false);
                    $this->asyncManagement->queuePendingAuthorization($transactionId);
                    break;
                case 'Authorized':
                    $payment->setIsTransactionClosed(false);
                    break;
                case 'Captured':
                    $this->asyncCharge->capture($order, $transactionId, $quote->getGrandTotal());
                    $payment->setIsTransactionClosed(true);
                    break;
            }
        }
    }
}
