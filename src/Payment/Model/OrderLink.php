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

use Amazon\Payment\Api\Data\OrderLinkInterface;
use Amazon\Payment\Model\ResourceModel\OrderLink as OrderLinkResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class OrderLink extends AbstractModel implements OrderLinkInterface
{
    protected function _construct()
    {
        $this->_init(OrderLinkResourceModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function setAmazonOrderReferenceId($amazonOrderReferenceId)
    {
        return $this->setData('amazon_order_reference_id', $amazonOrderReferenceId);
    }

    /**
     * {@inheritDoc}
     */
    public function getAmazonOrderReferenceId()
    {
        return $this->getData('amazon_order_reference_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData('order_id', $orderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderId()
    {
        return $this->getData('order_id');
    }
}
