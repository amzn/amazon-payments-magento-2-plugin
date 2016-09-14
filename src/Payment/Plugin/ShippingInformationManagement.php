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

class ShippingInformationManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var OrderInformationManagementInterface
     */
    protected $orderInformationManagement;

    public function __construct(
        OrderInformationManagementInterface $orderInformationManagement,
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository             = $cartRepository;
        $this->orderInformationManagement = $orderInformationManagement;
    }

    public function aroundSaveAddressInformation(
        ShippingInformationManagementInterface $shippingInformationManagement,
        Closure $proceed,
        $cartId,
        ShippingInformationInterface $shippingInformation
    ) {
        $return = $proceed($cartId, $shippingInformation);

        $quote                  = $this->cartRepository->getActive($cartId);
        $amazonOrderReferenceId = $quote->getExtensionAttributes()->getAmazonOrderReferenceId();

        if ($amazonOrderReferenceId) {
            $this->orderInformationManagement->saveOrderInformation(
                $amazonOrderReferenceId,
                [
                    AmazonConstraint::PAYMENT_PLAN_NOT_SET_ID,
                    AmazonConstraint::PAYMENT_METHOD_NOT_ALLOWED_ID
                ]
            );
        }

        return $return;
    }
}
