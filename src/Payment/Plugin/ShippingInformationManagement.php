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
namespace Amazon\Payment\Plugin;

use Amazon\Payment\Api\OrderInformationManagementInterface;
use Amazon\Payment\Domain\AmazonConstraint;
use Closure;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Amazon\Login\Helper\Session as LoginSessionHelper;

class ShippingInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var OrderInformationManagementInterface
     */
    private $orderInformationManagement;

    /**
     * @var LoginSessionHelper
     */
    private $loginSessionHelper;

    /**
     * ShippingInformationManagement constructor.
     *
     * @param LoginSessionHelper                  $loginSessionHelper
     * @param OrderInformationManagementInterface $orderInformationManagement
     * @param CartRepositoryInterface             $cartRepository
     */
    public function __construct(
        LoginSessionHelper $loginSessionHelper,
        OrderInformationManagementInterface $orderInformationManagement,
        CartRepositoryInterface $cartRepository
    ) {
        $this->loginSessionHelper         = $loginSessionHelper;
        $this->cartRepository             = $cartRepository;
        $this->orderInformationManagement = $orderInformationManagement;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $cartId,
        ShippingInformationInterface $shippingInformation
    ) {
        $return = [$cartId, $shippingInformation];

        $quote = $this->cartRepository->getActive($cartId);

        /* Grand total is 0, skip rest of the plugin */
        if ($quote->getGrandTotal() <= 0) {
            return $return;
        }

        // Add Amazon Order Reference ID only when logged in using Amazon Account
        $amazonCustomer = $this->loginSessionHelper->getAmazonCustomer();
        if (!$amazonCustomer) {
            return $return;
        }

        $amazonOrderReferenceId = $quote->getExtensionAttributes()
            ->getAmazonOrderReferenceId()
            ->getAmazonOrderReferenceId();

        if ($amazonOrderReferenceId) {
            $this->orderInformationManagement->saveOrderInformation(
                $amazonOrderReferenceId,
                [
                    AmazonConstraint::PAYMENT_PLAN_NOT_SET_ID,
                    AmazonConstraint::PAYMENT_METHOD_NOT_ALLOWED_ID
                ]
            );
        }

        /*
         * Magento\Quote\Model\Quote::setShippingAddress merges into the existing shipping address,
         *  rather than replacing it.  Because not all addresses have a region_id, make sure that
         *  the region_id is explicitly emptied, to prevent the old one being used.
         */
        $shippingAddress = $shippingInformation->getShippingAddress();
        if (!$shippingAddress->hasData('region_id')) {
            $shippingAddress->setRegionId("");
        }

        return $return;
    }
}
