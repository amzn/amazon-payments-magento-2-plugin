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
namespace Amazon\Core\Exception;

use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AmazonWebapiException extends Exception
{
    public function __construct(
        Phrase $phrase,
        $code = 0,
        $httpCode = self::HTTP_BAD_REQUEST,
        array $details = [],
        $name = '',
        $errors = null,
        $stackTrace = null
    ) {
        parent::__construct($phrase, $code, $httpCode, $details, $name, $errors, $stackTrace);
    }
}
