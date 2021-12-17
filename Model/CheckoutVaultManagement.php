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
     * @var \Amazon\Pay\Helper\Data
     */
    private $amazonHelper;

    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface
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
        \Amazon\Pay\Helper\Data $amazonHelper,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement,
        \Amazon\Pay\Logger\Logger $logger
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->amazonAdapter = $amazonAdapter;
        $this->magentoCheckoutSession = $magentoCheckoutSession;
        $this->cartManagement = $cartManagement;
        $this->amazonHelper = $amazonHelper;
        $this->orderRepository = $orderRepository;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->logger = $logger;
    }

    public function createCharge()
    {
        $quote = $this->magentoCheckoutSession->getQuote();
        $customerId = $quote->getCustomer()->getId();

        if (!$this->canCheckoutWithAmazon($quote)) {
            $this->logger->debug("Cannot checkout with Amazon");
            return [
                'success' => false,
                'message' => __("Unable to complete Amazon Pay checkout"),
            ];
        }

        try{
            $quote->collectTotals();
            $orderId = $this->cartManagement->placeOrder($quote->getId());
            $order = $this->orderRepository->get($orderId);
            
            $payment = $order->getPayment();
            $publicHash = $payment->getAdditionalInformation('public_hash');
            $customerId = $payment->getAdditionalInformation('customer_id');
            $token = $this->paymentTokenManagement->getByPublicHash($publicHash, $customerId);

            $changePermissionResult = $this->amazonAdapter->updateChargePermission(
                $order->getStoreId(),
                $token->getGatewayToken(),
                ['merchantReferenceId' => $order->getIncrementId()]
            );

        } catch (\Exception $e) {
            // cancel order
            if (isset($order)) {
                $this->cancelOrder($order);
            }

            throw $e;
        }
        return true; 
    }

     /**
     * @return bool
     */
    protected function canCheckoutWithAmazon($quote)
    {
        return $this->amazonConfig->isEnabled() &&
            !$this->amazonHelper->hasRestrictedProducts($quote);
    }

    /**
     * Cancel order
     *
     * @param $order
     */
    private function cancelOrder($order)
    {
        // set order as cancelled
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)->setStatus(
            \Magento\Sales\Model\Order::STATE_CANCELED
        );
        $order->getPayment()->setIsTransactionClosed(true);

        // cancel invoices
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoice->setState(Invoice::STATE_CANCELED);
        }

        // delete order comments and add new one
        foreach ($order->getStatusHistories() as $history) {
            $history->delete();
        }
        $order->addStatusHistoryComment(
            __('Payment was unable to be successfully captured.')
        );

        $order->save();
    }
}
