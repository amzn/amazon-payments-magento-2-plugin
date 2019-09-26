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

use Magento\Framework\Exception\NotFoundException;

/**
 * Class DownloadLog
 * Download log file via an admin link
 */
class DownloadLog extends \Magento\Backend\Controller\Adminhtml\System
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * DownloadLog constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        switch ($this->getRequest()->getParam('type')) {
            case 'client':
                $filePath = \Amazon\PayV2\Logger\Handler\Client::FILENAME;
                break;
            case 'async':
                $filePath = \Amazon\PayV2\Logger\Handler\AsyncIpn::FILENAME;
                break;
            default:
                $filePath = false;
                break;
        }

        if ($filePath) {
            $fileName = basename((string)$filePath);

            try {
                return $this->fileFactory->create(
                    $fileName,
                    [
                        'type' => 'filename',
                        'value' => $filePath
                    ]
                );
            } catch (\Exception $e) {
                throw new NotFoundException($e->getMessage());
            }
        }
    }

    /**
     * ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amazon_PayV2::downloadlogs');
    }
}
