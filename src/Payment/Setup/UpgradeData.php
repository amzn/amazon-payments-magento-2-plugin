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
namespace Amazon\Payment\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @param EavSetup $eavSetup
     */
    public function __construct(EavSetup $eavSetup)
    {
        $this->eavSetup = $eavSetup;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->upgradeAddressStreetMultiline();
        }
    }

    /**
     * @throws LocalizedException
     * @return void
     */
    private function upgradeAddressStreetMultiline()
    {
        $row = $this->eavSetup->getAttribute('customer_address', 'street', 'multiline_count');

        if ($row === false || ! is_numeric($row)) {
            throw new LocalizedException(__('Could not find the "multiline_count" config of the "street" Customer address attribute.'));
        }

        if ($row < 3) {
            $this->eavSetup->updateAttribute('customer_address', 'street', 'multiline_count', 3);
        }
    }
}
