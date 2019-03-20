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

use Amazon\Payment\Api\Data\PendingCaptureInterface;
use Amazon\Payment\Model\ResourceModel\PendingCapture as PendingCaptureResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;

class PendingCapture extends AbstractModel implements PendingCaptureInterface
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
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroup
     */
    private $filterGroup;

    /**
     * PendingCapture constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param DateTimeFactory       $dateFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchBuilder
     * @param FilterBuilder         $filterBuilder
     * @param FilterGroup           $filterGroup
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTimeFactory $dateFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchBuilder,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroup,
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
        $this->transactionRepository = $transactionRepository;
        $this->searchBuilder = $searchBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(PendingCaptureResourceModel::class);
    }

    /**
     * Populate order and payment ID properties
     *
     * @return bool
     * @throws \Exception
     */
    public function updateReferenceIds()
    {
        $parent = $this->filterBuilder
            ->setField('parent_txn_id')
            ->setValue($this->getCaptureId())
            ->setConditionType('eq')
            ->create();
        $child = $this->filterBuilder
            ->setField('txn_id')
            ->setValue($this->getCaptureId())
            ->setConditionType('eq')
            ->create();

        $filterOr = $this->filterGroup->setFilters([$parent, $child]);

        $searchCriteria = $this->searchBuilder->setFilterGroups([$filterOr])->create();

        $transactionList = $this->transactionRepository->getList($searchCriteria);

        foreach ($transactionList->getItems() as $transaction) {
            if ($transaction) {
                $this->setPaymentId($transaction->getPaymentId());
                $this->setOrderId($transaction->getOrderId());
                $this->save();
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getCaptureId()
    {
        return $this->getData(PendingCaptureInterface::CAPTURE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setCaptureId($captureId)
    {
        return $this->setData(PendingCaptureInterface::CAPTURE_ID, $captureId);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderId()
    {
        if (!$this->getData(PendingCaptureInterface::ORDER_ID)) {
            $this->updateReferenceIds();
        }
        return $this->getData(PendingCaptureInterface::ORDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(PendingCaptureInterface::ORDER_ID, $orderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentId()
    {
        if (!$this->getData(PendingCaptureInterface::PAYMENT_ID)) {
            $this->updateReferenceIds();
        }
        return $this->getData(PendingCaptureInterface::PAYMENT_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setPaymentId($paymentId)
    {
        return $this->setData(PendingCaptureInterface::PAYMENT_ID, $paymentId);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(PendingCaptureInterface::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(PendingCaptureInterface::CREATED_AT);
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
