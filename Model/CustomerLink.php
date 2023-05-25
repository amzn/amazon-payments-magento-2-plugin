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

use Amazon\Pay\Api\Data\CustomerLinkInterface;
use Amazon\Pay\Model\ResourceModel\CustomerLink as CustomerLinkResourceModel;
use Magento\Framework\Model\AbstractModel;

class CustomerLink extends AbstractModel implements CustomerLinkInterface
{
    /**
     * CustomerLink constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CustomerLinkResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function setAmazonId($amazonId)
    {
        return $this->setData(self::AMAZON_ID, $amazonId);
    }

    /**
     * @inheritDoc
     */
    public function getAmazonId()
    {
        return $this->getData(self::AMAZON_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }
}
