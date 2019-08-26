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
namespace Amazon\Alexa\Controller\Adminhtml\Alexa;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Download public key for Alexa
 */
class Download extends \Magento\Backend\Controller\Adminhtml\System
{
    /**
     * @var \Amazon\Alexa\Model\AlexaConfig
     */
    private $alexaConfig;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * Download constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Amazon\Alexa\Model\AlexaConfig $alexaConfig
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amazon\Alexa\Model\AlexaConfig $alexaConfig,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->alexaConfig = $alexaConfig;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Download public key file for Alexa
     */
    public function execute()
    {
        $publicKey = $this->alexaConfig->getAlexaPublicKey();
        return $this->fileFactory->create('amazon_public_key.pub', $publicKey, DirectoryList::TMP);
    }

    /**
     * ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amazon_Alexa::download');
    }
}
