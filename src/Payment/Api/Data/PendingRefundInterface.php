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
namespace Amazon\Payment\Api\Data;

use Amazon\Payment\Model\ResourceModel\PendingRefund as PendingRefundResource;

/**
 * @api
 */
interface PendingRefundInterface
{
    const ID = 'entity_id';
    const REFUND_ID = 'refund_id';
    const ORDER_ID = 'order_id';
    const PAYMENT_ID = 'payment_id';
    const CREATED_AT = 'created_at';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getRefundId();

    /**
     * @param string $refundId
     * @return $this
     */
    public function setRefundId($refundId);

    /**
     * @return integer
     */
    public function getPaymentId();

    /**
     * @param integer $paymentId
     * @return $this
     */
    public function setPaymentId($paymentId);

    /**
     * @return integer
     */
    public function getOrderId();

    /**
     * @param integer $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return $this
     * @throws \Exception
     */
    public function save();

    /**
     * @return $this
     * @throws \Exception
     */
    public function delete();

    /**
     * @param integer     $modelId
     * @param null|string $field
     * @return $this
     */
    public function load($modelId, $field = null);

    /**
     * Set whether to lock db record on load
     *
     * @param boolean $lockOnLoad
     * @return $this
     */
    public function setLockOnLoad($lockOnLoad);

    /**
     * Get whether to lock db record on load
     *
     * @return boolean
     */
    public function hasLockOnLoad();

    /**
     * @return PendingRefundResource
     */
    public function getResource();
}
