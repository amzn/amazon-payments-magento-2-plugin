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
namespace Amazon\Pay\Api\Data;

use Exception;

/**
 * @api
 */
interface AsyncInterface
{
    const ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const IS_PENDING = 'is_pending';
    const PENDING_ACTION = 'pending_action';
    const PENDING_ID = 'pending_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get entity id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get order id
     *
     * @return string
     */
    public function getOrderId();

    /**
     * Set order id
     *
     * @param string $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Is pending
     *
     * @return boolean
     */
    public function isPending();

    /**
     * Set pending
     *
     * @param boolean $isPending
     *
     * @return $this
     */
    public function setIsPending($isPending);

    /**
     * Get pending action
     *
     * @return integer
     */
    public function getPendingAction();

    /**
     * Set pending action
     *
     * @param string $pendingAction
     *
     * @return $this
     */
    public function setPendingAction($pendingAction);

    /**
     * Get pending id
     *
     * @return string
     */
    public function getPendingId();

    /**
     * Set pending id
     *
     * @param string $pendingId
     *
     * @return $this
     */
    public function setPendingId($pendingId);

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
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set created at
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Save async pending
     *
     * @return $this
     * @throws Exception
     */
    public function save();

    /**
     * Delete async pending
     *
     * @return $this
     * @throws Exception
     */
    public function delete();

    /**
     * Load async pending
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
     * @return \Amazon\Pay\Model\ResourceModel\Async
     */
    public function getResource();
}
