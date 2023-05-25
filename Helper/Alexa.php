<?php
/**
 * Copyright 2020 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Pay\Helper;

use Magento\Framework\Module\Dir;
use Magento\Framework\Serialize\SerializerInterface;

class Alexa
{
    /**
     * @var \Magento\Framework\Module\Dir
     */
    private $moduleDir;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csv;

    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Alexa constructor
     *
     * @param Dir $moduleDir
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Dir $moduleDir,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\Config\CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->moduleDir = $moduleDir;
        $this->csv = $csv;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * Get list of delivery carriers from cache or CSV file
     *
     * @return array
     */
    public function getDeliveryCarriers()
    {
        $cacheKey = hash('sha256', __METHOD__);
        $result = $this->cache->load($cacheKey);
        if ($result) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result = $this->serializer->unserialize(gzuncompress($result));
        }
        if (!$result) {
            $result = $this->fetchDeliveryCarriers();
            $this->cache->save(
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                gzcompress($this->serializer->serialize($result)),
                $cacheKey
            );
        }
        return $result;
    }

    /**
     * Load list of delivery carriers from CSV file
     *
     * @return array
     */
    protected function fetchDeliveryCarriers()
    {
        $result = [];
        $fileName = implode(DIRECTORY_SEPARATOR, [
            $this->moduleDir->getDir('Amazon_Pay', Dir::MODULE_ETC_DIR),
            'files',
            'amazon-pay-delivery-tracker-supported-carriers.csv'
        ]);
        foreach ($this->csv->getData($fileName) as $row) {
            list($carrierTitle, $carrierCode) = $row;
            $result[] = ['code' => $carrierCode, 'title' => $carrierTitle];
        }
        return $result;
    }
}
