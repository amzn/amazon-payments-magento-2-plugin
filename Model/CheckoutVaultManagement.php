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

namespace Amazon\Pay\Model;

use Amazon\Pay\Api\Data\CheckoutSessionInterface;
use Amazon\Pay\Gateway\Config\Config;
use Amazon\Pay\Model\Config\Source\AuthorizationMode;
use Amazon\Pay\Model\Config\Source\PaymentAction;
use Amazon\Pay\Model\AsyncManagement;
use http\Exception\UnexpectedValueException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Api\Data\TransactionInterface as Transaction;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\PaymentTokenFactory;

class CheckoutVaultManagement implements \Amazon\Pay\Api\CheckoutVaultManagementInterface
{
     /**
     * @var Adapter\AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $magentoCheckoutSession;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;


    /**
     * @var \Amazon\Pay\Logger\Logger
     */
    private $logger;


    public function __construct(
        \Magento\Checkout\Model\Session $magentoCheckoutSession,
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement,
        \Amazon\Pay\Logger\Logger $logger
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
        $this->magentoCheckoutSession = $magentoCheckoutSession;
        $this->cartManagement = $cartManagement;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->logger = $logger;
    }

    public function createCharge($publicHash)
    {
        $quote = $this->magentoCheckoutSession->getQuote();
        $customerId = $quote->getCustomer()->getId();

        $token = $this->paymentTokenManagement->getByPublicHash($publicHash, $customerId);
        if (!$token) return false;

        $result = $this->amazonAdapter->createCharge(
            $quote->getStoreId(),
            $token->getGatewayToken(),
            $quote->getGrandTotal(),
            $quote->getQuoteCurrencyCode()
        );
        $status = $result['status'] ?? '404';
        if (!preg_match('/^2\d\d$/', $status)) {
            // Something went wrong, but the order has already been placed, so cancelling it
            return false;
        }

        //$quote->collectTotals();

        //$orderId = $this->cartManagement->placeOrder($quote->getId());
        //$order = $this->orderRepository->get($orderId);


        //$this->amazonConfig->getCheckoutResultUrlPath();
        return true; 
    }
}
