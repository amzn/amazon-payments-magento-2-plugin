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
namespace Amazon\Login\Plugin;

use Amazon\Login\Api\CustomerLinkManagementInterface;
use Amazon\Login\Helper\Session as LoginSessionHelper;
use Amazon\Payment\Model\Method\Amazon as AmazonPaymentMethod;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderCustomerManagement
{
    /**
     * @var LoginSessionHelper
     */
    protected $loginSessionHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CustomerLinkManagementInterface
     */
    protected $customerLinkManagement;

    /**
     * @param LoginSessionHelper $loginSessionHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerLinkManagementInterface $customerLinkManagement
     */
    public function __construct(
        LoginSessionHelper $loginSessionHelper,
        OrderRepositoryInterface $orderRepository,
        CustomerLinkManagementInterface $customerLinkManagement
    ) {
        $this->loginSessionHelper     = $loginSessionHelper;
        $this->orderRepository        = $orderRepository;
        $this->customerLinkManagement = $customerLinkManagement;
    }

    /**
     * @param OrderCustomerManagementInterface $subject
     * @param \Closure $proceed
     * @param int $orderId
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreate(OrderCustomerManagementInterface $subject, \Closure $proceed, $orderId)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customerData */
        $customerData = $proceed($orderId);
        $paymentMethodName = $this->orderRepository->get($orderId)->getPayment()->getMethod();
        $isAmazonPayment = $paymentMethodName === AmazonPaymentMethod::PAYMENT_METHOD_CODE;
        $amazonCustomer = $this->loginSessionHelper->getAmazonCustomer();

        if ($isAmazonPayment && $amazonCustomer) {
            $this->customerLinkManagement->updateLink($customerData->getId(), $amazonCustomer->getId());
        }

        return $customerData;
    }
}
