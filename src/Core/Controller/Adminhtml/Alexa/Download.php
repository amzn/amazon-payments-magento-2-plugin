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

namespace Amazon\Core\Controller\Adminhtml\Alexa;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\Controller\Adminhtml\System;
use Amazon\Core\Helper\Data as AmazonCoreHelper;

/**
 * Download public key for Alexa
 */
class Download extends System
{
    /**
     * @var AmazonCoreHelper
     */
    protected $amazonCoreHelper;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * Download constructor.
     * @param Context $context
     * @param AmazonCoreHelper $amazonCoreHelper
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        AmazonCoreHelper $amazonCoreHelper,
        FileFactory $fileFactory
    ) {
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Download public key file for Alexa
     */
    public function execute()
    {
        $pubkey = $this->amazonCoreHelper->getAlexaPublicKey();
        return $this->fileFactory->create('amazon_public_key.pub', $pubkey, DirectoryList::TMP);
    }
}
