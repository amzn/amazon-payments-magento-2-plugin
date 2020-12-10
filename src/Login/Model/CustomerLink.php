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
namespace Amazon\Login\Model;

use Amazon\Login\Api\Data\CustomerLinkInterface;
use Amazon\Login\Model\ResourceModel\CustomerLink as CustomerLinkResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class CustomerLink extends AbstractModel implements CustomerLinkInterface
{
    protected function _construct()
    {
        $this->_init(CustomerLinkResourceModel::class);
    }

    /**
     * {@inheritDoc}
     */
    public function setAmazonId($amazonId)
    {
        return $this->setData('amazon_id', $amazonId);
    }

    /**
     * {@inheritDoc}
     */
    public function getAmazonId()
    {
        return $this->getData('amazon_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData('customer_id', $customerId);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }
}
