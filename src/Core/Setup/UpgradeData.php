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
namespace Amazon\Core\Setup;

use Amazon\Core\Helper\CategoryExclusion;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Setup\QuoteSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        // Used update query because all scopes needed to have this value updated and this is a fast, simple approach
        if (version_compare($context->getVersion(), '1.2.6', '<')) {

            $sql = "SELECT c.config_id 
                    FROM core_config_data c 
                    WHERE c.path = 'payment/amazon_payment/authorization_mode' AND c.value = 'asynchronous'";

            $result = $setup->getConnection()->fetchAll($sql);

            foreach ($result as $row) {
                $sql = "UPDATE core_config_data SET value='synchronous_possible' WHERE config_id = ".$row['config_id'];
                $setup->getConnection()->query($sql);

            }
        }
    }
}
