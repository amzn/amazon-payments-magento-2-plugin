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
namespace Amazon\Pay\Model\ResourceModel\Async;

use Generator;
use Amazon\Pay\Api\Data\AsyncInterface;
use Amazon\Pay\Model\Async as AsyncModel;
use Amazon\Pay\Model\ResourceModel\Async as AsyncResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AsyncModel::class, AsyncResourceModel::class);
    }
}
