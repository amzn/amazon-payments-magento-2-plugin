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
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(QuoteSetupFactory $quoteSetupFactory)
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.0', '<=')) {
            $this->addQuoteTableDirtyFlagAttribute($setup);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function addQuoteTableDirtyFlagAttribute(ModuleDataSetupInterface $setup)
    {
        $options = [
            'type' => Table::TYPE_SMALLINT,
            'visible' => false,
            'required' => false,
            'default' => 0,
            'nullable' => false,
            'unsigned' => true
        ];

        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);

        $quoteSetup->addAttribute('quote_item', CategoryExclusion::ATTR_QUOTE_ITEM_IS_EXCLUDED_PRODUCT, $options);
    }
}
