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

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Amazon\Pay\Api\CustomerLinkRepositoryInterface;
use Amazon\Pay\Api\Data;
use Amazon\Pay\Api\Data\CustomerLinkInterface;
use Amazon\Pay\Api\Data\CustomerLinkSearchResultsInterfaceFactory;
use Amazon\Pay\Model\ResourceModel\CustomerLink as CustomerLinkResourceModel;
use Amazon\Pay\Model\ResourceModel\CustomerLink\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerLinkRepository implements CustomerLinkRepositoryInterface
{
    /**
     * @var CustomerLinkResourceModel
     */
    private $resourceModel;

    /**
     * @var CustomerLinkFactory
     */
    private $customerLinkFactory;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * CustomerLinkRepository constructor
     *
     * @param CustomerLinkResourceModel $resourceModel
     * @param CustomerLinkFactory $customerLinkFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerLinkSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        CustomerLinkResourceModel $resourceModel,
        CustomerLinkFactory $customerLinkFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerLinkSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resourceModel = $resourceModel;
        $this->customerLinkFactory = $customerLinkFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function get($customerId)
    {
        $customerLink = $this->customerLinkFactory->create();
        $this->resourceModel->load($customerLink, $customerId, 'customer_id');
        return $customerLink;
    }

    /**
     * @inheritDoc
     */
    public function getById($entityId)
    {
        $customerLink = $this->customerLinkFactory->create();
        $this->resourceModel->load($customerLink, $entityId);
        return $customerLink;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(CustomerLinkInterface $customerLink)
    {
        try {
            $this->resourceModel->delete($customerLink);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }

    /**
     * @inheritDoc
     */
    public function save(CustomerLinkInterface $customerLink)
    {
        try {
            $this->resourceModel->save($customerLink);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save Amazon customer link: %1', $exception->getMessage()),
                $exception
            );
        }
        return $customerLink;
    }
}
