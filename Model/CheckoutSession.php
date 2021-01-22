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

use Amazon\Pay\Api\Data\CheckoutSessionInterface;
use Magento\Framework\Model\AbstractModel;

class CheckoutSession extends AbstractModel implements CheckoutSessionInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateFactory;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dateFactory = $dateFactory;
        $this->setData(self::KEY_IS_ACTIVE, true);
        $this->setData(self::KEY_CREATED_AT, $this->dateFactory->create()->gmtDate());
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(\Amazon\Pay\Model\ResourceModel\CheckoutSession::class);
    }

    /**
     * @inheritDoc
     */
    public function getSessionId()
    {
        return $this->getData(self::KEY_SESSION_ID);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId()
    {
        return $this->getData(self::KEY_QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId($value)
    {
        return $this->setData(self::KEY_QUOTE_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getIsActive()
    {
        return $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::KEY_CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getCanceledAt()
    {
        return $this->getData(self::KEY_CANCELED_AT);
    }

    /**
     * @inheritDoc
     */
    public function cancel()
    {
        $this->setData(self::KEY_IS_ACTIVE, false);
        $this->setData(self::KEY_CANCELED_AT, $this->dateFactory->create()->gmtDate());
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdated()
    {
        $this->setData(self::KEY_UPDATED_AT, $this->dateFactory->create()->gmtDate());
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCompletedAt()
    {
        return $this->getData(self::KEY_COMPLETED_AT);
    }

    /**
     * @inheritDoc
     */
    public function complete()
    {
        $this->setData(self::KEY_IS_ACTIVE, false);
        $this->setData(self::KEY_COMPLETED_AT, $this->dateFactory->create()->gmtDate());
        return $this;
    }
}
