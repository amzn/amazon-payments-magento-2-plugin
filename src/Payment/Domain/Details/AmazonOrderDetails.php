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

use Amazon\Payment\Domain\AmazonOrderStatus;
use Amazon\Payment\Domain\AmazonOrderStatusFactory;

class AmazonOrderDetails
{
    /**
     * @var string
     */
    private $orderReferenceId;

    /**
     * @var AmazonOrderStatus
     */
    private $status;

    /**
     * AmazonOrderDetails constructor.
     *
     * @param AmazonOrderStatusFactory $amazonOrderStatusFactory
     * @param array                    $details
     */
    public function __construct(AmazonOrderStatusFactory $amazonOrderStatusFactory, array $details)
    {
        $status       = $details['OrderReferenceStatus'];
        $this->status = $amazonOrderStatusFactory->create(
            [
            'state'      => $status['State'],
            'reasonCode' => (isset($status['ReasonCode']) ? $status['ReasonCode'] : null)
            ]
        );

        $this->orderReferenceId = $details['AmazonOrderReferenceId'];
    }

    /**
     * Get status
     *
     * @return AmazonOrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get order reference id
     *
     * @return string
     */
    public function getOrderReferenceId()
    {
        return $this->orderReferenceId;
    }
}
