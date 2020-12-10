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

namespace Amazon\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\ProductMetadata;
use Amazon\Payment\Gateway\Helper\SubjectReader;
use Amazon\Core\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amazon\Payment\Gateway\Data\Order\OrderAdapterFactory;

/**
 * Class RefundRequestBuilder
 * Builds refund request for Amazon Pay
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class RefundRequestBuilder implements BuilderInterface
{

    /**
     * @var ProductMetadata
     */
    private $productMetaData;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Data
     */
    private $coreHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * RefundRequestBuilder constructor.
     *
     * @param ProductMetadata $productMetadata
     * @param SubjectReader $subjectReader
     * @param Data $coreHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderAdapterFactory $orderAdapterFactory
     */
    public function __construct(
        ProductMetaData $productMetadata,
        SubjectReader $subjectReader,
        Data $coreHelper,
        OrderRepositoryInterface $orderRepository,
        OrderAdapterFactory $orderAdapterFactory
    ) {
        $this->coreHelper = $coreHelper;
        $this->productMetaData = $productMetadata;
        $this->subjectReader = $subjectReader;
        $this->orderRepository = $orderRepository;
        $this->orderAdapterFactory = $orderAdapterFactory;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $data = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $orderDO = $paymentDO->getOrder();

        $currencyCode = $payment->getOrder()->getOrderCurrencyCode();
        $total = $payment->getCreditMemo()->getGrandTotal();
        $storeId = $orderDO->getStoreId();

        // The magento order adapter doesn't expose everything we need to send a request to the AP API so we
        // need to use our own version with the details we need exposed in custom methods.
        $orderAdapter = $this->orderAdapterFactory->create(
            ['order' => $payment->getOrder()]
        );

        $amazonId = $orderAdapter->getAmazonOrderID();
        $multicurrency = $orderAdapter->getMulticurrencyDetails($total);

        if ($multicurrency['multicurrency']) {
            $currencyCode = $multicurrency['order_currency'];
            $total = $multicurrency['total'];
            $storeId = $multicurrency['store_id'];
        }

        if ($amazonId) {
            $data = [
                'amazon_capture_id' => $payment->getParentTransactionId(),
                'refund_reference_id' => $amazonId . '-R' . time(),
                'refund_amount' => $total,
                'currency_code' => $currencyCode,
                'store_id' => $storeId
            ];
        }

        return $data;
    }
}
