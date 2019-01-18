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

use Amazon\Payment\Gateway\Config\Config;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\ProductMetadata;
use Amazon\Payment\Gateway\Helper\SubjectReader;
use Amazon\Core\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class SettlementRequestBuilder implements BuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

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
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * SettlementRequestBuilder constructor.
     *
     * @param Config $config
     * @param ProductMetadata $productMetadata
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param SubjectReader $subjectReader
     * @param Data $coreHelper
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        ProductMetaData $productMetadata,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository,
        SubjectReader $subjectReader,
        Data $coreHelper,
        Logger $logger
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->coreHelper = $coreHelper;
        $this->productMetaData = $productMetadata;
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }


    /**
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        $data = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $orderDO = $paymentDO->getOrder();

        $currencyCode = $orderDO->getCurrencyCode();
        $total = $buildSubject['amount'];

        if ($buildSubject['multicurrency']['multicurrency']) {
            $currencyCode = $buildSubject['multicurrency']['order_currency'];
            $total = $buildSubject['multicurrency']['total'];
        }


        if (isset($buildSubject['amazon_order_id']) && $buildSubject['amazon_order_id']) {
                $data = [
                    'amazon_authorization_id' => $paymentDO->getPayment()->getParentTransactionId(),
                    'capture_amount' => $total,
                    'currency_code' => $currencyCode,
                    'amazon_order_reference_id' => $buildSubject['amazon_order_id'],
                    'store_id' => $buildSubject['multicurrency']['store_id'],
                    'store_name' => $buildSubject['multicurrency']['store_name'],
                    'custom_information' =>
                        'Magento Version : ' . $this->productMetaData->getVersion() . ' ' .
                        'Plugin Version : ' . $this->coreHelper->getVersion(),
                    'platform_id' => $this->config->getValue('platform_id'),
                    'request_payment_authorization' => false
                ];

                if (isset($buildSubject['request_payment_authorization']) && $buildSubject['request_payment_authorization']) {
                    $data['request_payment_authorization'] = true;
                }
        }

        return $data;
    }
}
