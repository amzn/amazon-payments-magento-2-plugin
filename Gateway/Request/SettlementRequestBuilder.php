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

namespace Amazon\Pay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Amazon\Pay\Model\AmazonConfig;
use Amazon\Pay\Gateway\Helper\SubjectReader;

class SettlementRequestBuilder implements BuilderInterface
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * SettlementRequestBuilder constructor.
     *
     * @param AmazonConfig $amazonConfig
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        AmazonConfig $amazonConfig,
        SubjectReader $subjectReader
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Get invoice associated with payment
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Sales\Model\Order\Invoice
     */
    protected function getCurrentInvoice($payment)
    {
        $result = null;
        $order = $payment->getOrder();
        foreach ($order->getInvoiceCollection() as $invoice) {
            if (!$invoice->getId()) {
                $result = $invoice;
                break;
            }
        }
        return $result;
    }

    /**
     * Get comment associated with invoice
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return string
     */
    protected function getComment($payment)
    {
        $result = '';
        $invoice = $this->getCurrentInvoice($payment);
        if ($invoice && $invoice->getComments()) {
            foreach ($invoice->getComments() as $comment) {
                if ($comment->getComment()) {
                    $result = $comment->getComment();
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Get Amazon Pay headers associated with invoice
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return array
     */
    protected function getHeaders($payment)
    {
        $result = [];
        $data = (array) json_decode($this->getComment($payment), true);
        foreach ($data as $key => $value) {
            if (strpos($key, 'x-amz-pay') === 0) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Get charge ID from payment transaction information
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return string
     */
    protected function getChargeId($payment)
    {
        $chargeId = '';

        if ($parentTransactionId = $payment->getParentTransactionId()) {
            $chargeId = rtrim($parentTransactionId, '-capture');
            return $chargeId;
        }

        if (preg_match('/[A-Z]..-[0-9]*-[0-9]*-C[0-9]*/', $payment->getLastTransId(), $matches)) {
            $chargeId = array_shift($matches);
        }

        return $chargeId;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        // Used for Settlement and Refund

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $orderDO = $paymentDO->getOrder();
        $storeId = $orderDO->getStoreId();
        $order = $paymentDO->getPayment()->getOrder();
        $payment = $paymentDO->getPayment();

        $currencyCode = $order->getOrderCurrencyCode();
        if ($payment->getAmazonDisplayInvoiceAmount()) {
            $total = $payment->getAmazonDisplayInvoiceAmount();
        } elseif ($creditMemo = $payment->getCreditMemo()) {
            $total = $creditMemo->getGrandTotal();
        } else {
            $total = $payment->getAmountOrdered();
        }

        $data = [
            'store_id' => $storeId,
            'charge_id' => $this->getChargeId($payment),
            'amount' => $total,
            'currency_code' => $currencyCode,
        ];
        if ($this->amazonConfig->isSandboxEnabled(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $orderDO->getStoreId()
        )) {
            $data['headers'] = $this->getHeaders($paymentDO->getPayment());
        }

        return $data;
    }
}
