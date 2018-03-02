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

use Amazon\Payment\Model\ResourceModel\PendingCapture as PendingCaptureResourceModel;
use Exception;

/**
 * @api
 */
interface PendingCaptureInterface
{
    const ID = 'entity_id';
    const CAPTURE_ID = 'capture_id';
    const ORDER_ID = 'order_id';
    const PAYMENT_ID = 'payment_id';
    const CREATED_AT = 'created_at';

    /**
     * Get pending capture id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get capture id
     *
     * @return string
     */
    public function getCaptureId();

    /**
     * Set capture id
     *
     * @param string $captureId
     *
     * @return $this
     */
    public function setCaptureId($captureId);

    /**
     * Get payment id
     *
     * @return integer
     */
    public function getPaymentId();

    /**
     * Set payment id
     *
     * @param integer $paymentId
     *
     * @return $this
     */
    public function setPaymentId($paymentId);

    /**
     * Get order id
     *
     * @return integer
     */
    public function getOrderId();

    /**
     * Set order id
     *
     * @param integer $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Save pending capture
     *
     * @return $this
     * @throws Exception
     */
    public function save();

    /**
     * Delete pending capture
     *
     * @return $this
     * @throws Exception
     */
    public function delete();

    /**
     * Load pending capture data
     *
     * @param integer     $modelId
     * @param null|string $field
     *
     * @return $this
     */
    public function load($modelId, $field = null);

    /**
     * Set whether to lock db record on load
     *
     * @param boolean $lockOnLoad
     *
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
     * Retrieve model resource
     *
     * @return PendingCaptureResourceModel
     */
    public function getResource();
}
