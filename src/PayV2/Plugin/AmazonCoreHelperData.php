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
namespace Amazon\PayV2\Plugin;

use Amazon\Core\Helper\Data;

class AmazonCoreHelperData
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     */
    private $amazonConfig;

    /**
     * AmazonCoreHelperData constructor.
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     */
    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig
    ) {
        $this->amazonConfig = $amazonConfig;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsPwaEnabled(Data $subject, $result)
    {
        // Disable v1 PWA
        if ($result && $this->amazonConfig->getApiVersion() == '2') {
            return false;
        }

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsLwaEnabled(Data $subject, $result)
    {
        // Disable v1 LWA
        if ($result && $this->amazonConfig->getApiVersion() == '2') {
            return false;
        }

        return $result;
    }
}
