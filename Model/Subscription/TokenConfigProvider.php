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

namespace Amazon\Pay\Model\Subscription;

use Amazon\Pay\Model\AmazonConfig;
use Magento\Checkout\Model\ConfigProviderInterface;

class TokenConfigProvider implements ConfigProviderInterface
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @param AmazonConfig $amazonConfig
     */
    public function __construct($amazonConfig)
    {
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * Get Amazon config
     *
     * @return array
     */
    public function getIcon()
    {
        return $this->amazonConfig->getAmazonIcon();
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return [];
    }
}
