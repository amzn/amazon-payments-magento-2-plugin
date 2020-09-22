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
namespace Amazon\PayV2\Plugin;

use Amazon\PayV2\Helper\Data;
use Amazon\PayV2\Api\CustomerLinkManagementInterface;
use Amazon\PayV2\Helper\Session as LoginSessionHelper;
use Amazon\PayV2\Gateway\Config\Config;
use Amazon\PayV2\Model\AmazonConfig;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderCustomerManagement
{
    /**
     * @var LoginSessionHelper
     */
    private $loginSessionHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerLinkManagementInterface
     */
    private $customerLinkManagement;

    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @param LoginSessionHelper $loginSessionHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerLinkManagementInterface $customerLinkManagement
     * @param Data $amazonConfig
     */
    public function __construct(
        LoginSessionHelper $loginSessionHelper,
        OrderRepositoryInterface $orderRepository,
        CustomerLinkManagementInterface $customerLinkManagement,
        Data $amazonConfig
    ) {
        $this->loginSessionHelper     = $loginSessionHelper;
        $this->orderRepository        = $orderRepository;
        $this->customerLinkManagement = $customerLinkManagement;
        $this->amazonConfig             = $amazonConfig;
    }

    /**
     * @param OrderCustomerManagementInterface $subject
     * @param CustomerInterface $result
     * @param int $orderId
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(OrderCustomerManagementInterface $subject, $result, $orderId)
    {
        if ($this->amazonConfig->isLwaEnabled()) {
            $paymentMethodName = $this->orderRepository->get($orderId)->getPayment()->getMethod();
            $isAmazonPayment   = $paymentMethodName === Config::CODE;
            $amazonCustomer    = $this->loginSessionHelper->getAmazonCustomer();

            if ($isAmazonPayment && $amazonCustomer) {
                $this->customerLinkManagement->updateLink($result->getId(), $amazonCustomer->getId());
            }
        }

        return $result;
    }
}
