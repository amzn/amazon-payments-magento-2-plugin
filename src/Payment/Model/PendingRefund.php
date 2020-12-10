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
namespace Amazon\Payment\Model;

use Amazon\Payment\Api\Data\PendingRefundInterface;
use Amazon\Payment\Model\ResourceModel\PendingRefund as PendingRefundResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class PendingRefund extends AbstractModel implements PendingRefundInterface
{
    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var boolean
     */
    private $lockOnLoad = false;

    /**
     * @param Context               $context
     * @param Registry              $registry
     * @param DateTimeFactory       $dateFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTimeFactory $dateFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
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
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(PendingRefundResourceModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getRefundId()
    {
        return $this->getData(PendingRefundInterface::REFUND_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setRefundId($refundId)
    {
        return $this->setData(PendingRefundInterface::REFUND_ID, $refundId);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderId()
    {
        return $this->getData(PendingRefundInterface::ORDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(PendingRefundInterface::ORDER_ID, $orderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentId()
    {
        return $this->getData(PendingRefundInterface::PAYMENT_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setPaymentId($paymentId)
    {
        return $this->setData(PendingRefundInterface::PAYMENT_ID, $paymentId);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(PendingRefundInterface::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(PendingRefundInterface::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave()
    {
        if (! $this->getId()) {
            $this->setCreatedAt($this->dateFactory->create()->gmtDate());
        }

        return parent::beforeSave();
    }

    /**
     * {@inheritDoc}
     */
    public function setLockOnLoad($lockOnLoad)
    {
        $this->lockOnLoad = $lockOnLoad;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasLockOnLoad()
    {
        return $this->lockOnLoad;
    }
}
