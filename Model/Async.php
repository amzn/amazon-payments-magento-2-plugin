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

use Amazon\Pay\Model\ResourceModel\Async as AsyncResourceModel;
use Magento\Framework\Model\AbstractModel;

class Async extends AbstractModel implements \Amazon\Pay\Api\Data\AsyncInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var boolean
     */
    private $lockOnLoad = false;

    /**
     * Async constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->dateFactory = $dateFactory;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(AsyncResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function isPending()
    {
        return (bool)$this->getData(self::IS_PENDING);
    }

    /**
     * @inheritDoc
     */
    public function setIsPending($isPending)
    {
        return $this->setData(self::IS_PENDING, $isPending);
    }

    /**
     * @inheritDoc
     */
    public function getPendingAction()
    {
        return $this->getData(self::PENDING_ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setPendingAction($pendingAction)
    {
        return $this->setData(self::PENDING_ACTION, $pendingAction);
    }

    /**
     * @inheritDoc
     */
    public function getPendingId()
    {
        return $this->getData(self::PENDING_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPendingId($pendingId)
    {
        return $this->setData(self::PENDING_ID, $pendingId);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        if (!$this->getId()) {
            $this->setCreatedAt($this->dateFactory->create()->gmtDate());
            $this->setIsPending(true);
        }

        $this->setUpdatedAt($this->dateFactory->create()->gmtDate());

        return parent::beforeSave();
    }

    /**
     * @inheritDoc
     */
    public function setLockOnLoad($lockOnLoad)
    {
        $this->lockOnLoad = $lockOnLoad;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasLockOnLoad()
    {
        return $this->lockOnLoad;
    }
}
