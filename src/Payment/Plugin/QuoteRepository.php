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

use Amazon\Core\Helper\Data;
use Amazon\Payment\Api\QuoteLinkManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class QuoteRepository
{
    /**
     * @var QuoteLinkManagementInterface
     */
    private $quoteLinkManagement;

    /**
     * @var Data
     */
    private $coreHelper;

    public function __construct(
        QuoteLinkManagementInterface $quoteLinkManagement,
        Data $coreHelper
    ) {
        $this->quoteLinkManagement  = $quoteLinkManagement;
        $this->coreHelper           = $coreHelper;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CartRepositoryInterface $cartRepository, CartInterface $cart)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $this->quoteLinkManagement->setAmazonOrderReferenceIdExtensionAttribute($cart);
        }

        return $cart;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetForCustomer(CartRepositoryInterface $cartRepository, CartInterface $cart)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $this->quoteLinkManagement->setAmazonOrderReferenceIdExtensionAttribute($cart);
        }

        return $cart;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetActive(CartRepositoryInterface $cartRepository, CartInterface $cart)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $this->quoteLinkManagement->setAmazonOrderReferenceIdExtensionAttribute($cart);
        }

        return $cart;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetActiveForCustomer(CartRepositoryInterface $cartRepository, CartInterface $cart)
    {
        if ($this->coreHelper->isPwaEnabled()) {
            $this->quoteLinkManagement->setAmazonOrderReferenceIdExtensionAttribute($cart);
        }

        return $cart;
    }
}
