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
namespace Amazon\Alexa\Setup;

use Amazon\Alexa\Model\ResourceModel\AlexaCarrier;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 */
class InstallData implements InstallDataInterface
{
    /**
     * Mappings of carrier titles to codes
     */
    const CSV = 'files/amazon-pay-delivery-tracker-supported-carriers.csv';

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csv;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * InstallData constructor.
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->csv          = $csv;
        $this->moduleReader = $moduleReader;
    }

    /**
     * Install Carrier Titles & Codes
     *
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $carriers = $this->getCarriersFromCsv();

        $adapter = $setup->getConnection();
        $setup->startSetup();
        $adapter->insertArray(AlexaCarrier::TABLE_NAME, ['carrier_title', 'carrier_code'], $carriers);
        $setup->endSetup();
    }

    /**
     * Load carriers from CSV file
     *
     * @return array
     */
    private function getCarriersFromCsv()
    {
        $fileDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_SETUP_DIR,
            'Amazon_Alexa'
        );

        return $this->csv->getData($fileDir . DIRECTORY_SEPARATOR . self::CSV);
    }
}
