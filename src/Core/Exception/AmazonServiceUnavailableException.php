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

use Magento\Framework\Exception\LocalizedException;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AmazonServiceUnavailableException extends LocalizedException
{
    const ERROR_MESSAGE = 'Amazon could not process your request.';

    /**
     * @var string
     */
    private $apiErrorType;

    /**
     * @var string
     */
    private $apiErrorCode;

    /**
     * @var string
     */
    private $apiErrorMessage;

    /**
     * AmazonServiceUnavailableException constructor.
     * @param string $apiErrorType
     * @param string $apiErrorCode
     * @param string $apiErrorMessage
     */
    public function __construct($apiErrorType = '', $apiErrorCode = '', $apiErrorMessage = '')
    {
        $this->apiErrorType = $apiErrorType;
        $this->apiErrorCode = $apiErrorCode;
        $this->apiErrorMessage = $apiErrorMessage;
        parent::__construct(__('Amazon could not process your request.'));
    }

    /**
     * @return string
     */
    public function getApiErrorType() {
        return $this->apiErrorType;
    }

    /**
     * @return string
     */
    public function getApiErrorCode() {
        return $this->apiErrorCode;
    }

    /**
     * @return string
     */
    public function getApiErrorMessage() {
        return $this->apiErrorMessage;
    }
}
