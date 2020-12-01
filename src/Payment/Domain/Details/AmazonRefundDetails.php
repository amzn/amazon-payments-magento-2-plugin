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
namespace Amazon\Payment\Domain\Details;

use Amazon\Payment\Domain\AmazonRefundStatus;
use Amazon\Payment\Domain\AmazonRefundStatusFactory;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AmazonRefundDetails
{
    /**
     * @var AmazonRefundStatus
     */
    private $refundStatus;

    /**
     * @var string|null
     */
    private $refundId;

    /**
     * @param AmazonRefundStatusFactory $amazonRefundStatusFactory
     * @param array $details
     */
    public function __construct(
        AmazonRefundStatusFactory $amazonRefundStatusFactory,
        array $details
    ) {
        $statusData = $details['RefundStatus'];

        $this->refundStatus = $amazonRefundStatusFactory->create([
            'state'      => $statusData['State'],
            'reasonCode' => isset($statusData['ReasonCode']) ? $statusData['ReasonCode'] : null
        ]);

        if (isset($details['AmazonRefundId'])) {
            $this->refundId = $details['AmazonRefundId'];
        }
    }

    /**
     * @return AmazonRefundStatus
     */
    public function getRefundStatus()
    {
        return $this->refundStatus;
    }

    /**
     * @return string|null
     */
    public function getRefundId()
    {
        return $this->refundId;
    }

    /**
     * @return bool
     */
    public function isRefundPending()
    {
        return $this->refundStatus->getState() === AmazonRefundStatus::STATE_PENDING;
    }

    /**
     * @return bool
     */
    public function isRefundCompleted()
    {
        return $this->refundStatus->getState() === AmazonRefundStatus::STATE_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isRefundDeclined()
    {
        return $this->refundStatus->getState() === AmazonRefundStatus::STATE_DECLINED;
    }
}
