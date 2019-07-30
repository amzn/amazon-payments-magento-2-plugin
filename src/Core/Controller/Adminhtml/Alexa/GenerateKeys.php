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
use Magento\Backend\Controller\Adminhtml\System;
use Magento\Framework\Message\ManagerInterface;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Core\Model\Alexa;

/**
 * Generate public/private key pairs for Alexa
 */
class GenerateKeys extends System
{
    /**
     * @var AmazonCoreHelper
     */
    protected $amazonCoreHelper;

    /**
     * @var Alexa
     */
    protected $alexaModel;

    /**
     * @var Alexa
     */
    protected $messageManager;

    /**
     * GenerateKeys constructor.
     * @param Context $context
     * @param AmazonCoreHelper $amazonCoreHelper
     * @param Alexa $alexaModel
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        AmazonCoreHelper $amazonCoreHelper,
        Alexa $alexaModel,
        ManagerInterface $messageManager
    ) {
        $this->amazonCoreHelper = $amazonCoreHelper;
        $this->alexaModel       = $alexaModel;
        $this->messageManager   = $messageManager;
        parent::__construct($context);
    }

    /**
     * Generate private/public keypairs for Alexa
     */
    public function execute()
    {
        $this->alexaModel->generateKeys();

        $this->messageManager->addSuccess(
            __('Your Amazon Pay public/private key pair has been generated for Alexa Delivery Notification.')
        );
        $this->_redirect('adminhtml/system_config/edit/section/payment');
    }
}
