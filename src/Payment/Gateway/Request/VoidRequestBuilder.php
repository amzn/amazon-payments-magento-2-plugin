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

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class VoidRequestBuilder implements BuilderInterface
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
     * VoidRequestBuilder constructor.
     *
     * @param ProductMetadata          $productMetadata
     * @param SubjectReader            $subjectReader
     * @param Data                     $coreHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ProductMetaData $productMetadata,
        SubjectReader $subjectReader,
        Data $coreHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->coreHelper = $coreHelper;
        $this->productMetaData = $productMetadata;
        $this->subjectReader = $subjectReader;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Builds ENV request
     *
     * @param  array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $data = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $orderDO = $paymentDO->getOrder();

        $order = $this->orderRepository->get($orderDO->getId());

        if ($order) {
            $quoteLink = $this->subjectReader->getQuoteLink($order->getQuoteId());

            if ($quoteLink) {
                $data = [
                    'store_id' => $order->getStoreId(),
                    'amazon_order_reference_id' => $quoteLink->getAmazonOrderReferenceId()
                ];
            }
        }

        return $data;
    }
}
