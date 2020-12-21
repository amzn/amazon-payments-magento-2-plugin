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

namespace Amazon\PayV2\Model;

use Amazon\PayV2\Api\CheckoutSessionRepositoryInterface;
use Amazon\PayV2\Api\Data\CheckoutSessionInterface;
use Magento\Quote\Api\Data\CartInterface;

class CheckoutSessionRepository implements CheckoutSessionRepositoryInterface
{

    /**
     * @var ResourceModel\CheckoutSession
     */
    private $checkoutSessionResourceModel;

    /**
     * @var ResourceModel\CheckoutSession\CollectionFactory
     */
    private $checkoutSessionCollectionFactory;

    /**
     * @var array
     */
    private $checkoutSessions = [];

    public function __construct(
        ResourceModel\CheckoutSession $checkoutSessionResourceModel,
        ResourceModel\CheckoutSession\CollectionFactory $checkoutSessionCollectionFactory
    ) {
        $this->checkoutSessionResourceModel = $checkoutSessionResourceModel;
        $this->checkoutSessionCollectionFactory = $checkoutSessionCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getActiveForCart(CartInterface $cart)
    {
        if (!array_key_exists($cart->getId(), $this->checkoutSessions)) {
            $result = null;
            $checkoutSessionCollection = $this->checkoutSessionCollectionFactory->create();
            /* @var $checkoutSessionCollection ResourceModel\CheckoutSession\Collection */
            $checkoutSessionCollection->addFieldToFilter(CheckoutSessionInterface::KEY_QUOTE_ID, $cart->getId());
            $checkoutSessionCollection->addFieldToFilter(CheckoutSessionInterface::KEY_IS_ACTIVE, true);
            $checkoutSessionCollection->setOrder(CheckoutSessionInterface::KEY_ID);
            $checkoutSessionCollection->setPageSize(1);
            if ($checkoutSessionCollection->count()) {
                $result = $checkoutSessionCollection->getFirstItem();
            }
            $this->checkoutSessions[$cart->getId()] = $result;
        }
        return $this->checkoutSessions[$cart->getId()];
    }

    /**
     * @inheritDoc
     */
    public function save(CheckoutSessionInterface $checkoutSession)
    {
        $this->checkoutSessionResourceModel->save($checkoutSession);
    }
}
