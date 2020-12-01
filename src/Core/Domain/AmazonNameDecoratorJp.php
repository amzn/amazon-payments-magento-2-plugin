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

namespace Amazon\Core\Domain;

use Amazon\Core\Api\Data\AmazonNameInterface;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AmazonNameDecoratorJp implements AmazonNameInterface
{
    /**
     * @var AmazonNameInterface
     */
    private $amazonName;

    /**
     * @param AmazonNameInterface $amazonName
     */
    public function __construct(AmazonNameInterface $amazonName)
    {
        $this->amazonName = $amazonName;
    }

    /**
     * @inheritdoc
     */
    public function getFirstName()
    {
        return $this->convertKana($this->amazonName->getLastName());
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->convertKana($this->amazonName->getFirstName());
    }

    /**
     * Convert to UTF-8 Kana
     *
     * @param $string
     *
     * @return string
     */
    private function convertKana($string)
    {
        return mb_convert_kana($string, 's', 'utf-8');
    }
}
