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
namespace Amazon\PayV2\Controller\Adminhtml\PayV2;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Download public key
 */
class Download extends \Magento\Backend\Controller\Adminhtml\System
{
    /**
     * @var \©Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * Download constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \©Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Download public key file
     */
    public function execute()
    {
        $publicKey = $this->amazonConfig->getPublicKey();
        return $this->fileFactory->create('amazon_public_key.pub', $publicKey, DirectoryList::TMP);
    }

    /**
     * ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amazon_PayV2::download');
    }
}
