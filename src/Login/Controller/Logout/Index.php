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
namespace Amazon\Login\Controller\Logout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Amazon\Login\Helper\Session;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Session
     */
    private $sessionHelper;

    /**
     * @param Context     $context
     * @param JsonFactory $jsonFactory
     * @param Session     $sessionHelper
     */
    public function __construct(Context $context, JsonFactory $jsonFactory, Session $sessionHelper)
    {
        parent::__construct($context);
        $this->jsonFactory   = $jsonFactory;
        $this->sessionHelper = $sessionHelper;
    }

    public function execute()
    {
        $this->sessionHelper->setIsAmazonLoggedIn(false);
        return $this->jsonFactory->create();
    }
}
