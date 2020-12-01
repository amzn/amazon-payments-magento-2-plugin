<?php
/**
 * Copyright 2017 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 */

namespace Amazon\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Displays links to available custom logs
 *
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class DeveloperLogs extends \Magento\Config\Block\System\Config\Form\Field
{
    const DOWNLOAD_PATH = 'amazonlogs/download';

    const LOGS = [
        'ipnLog' => ['name' => 'IPN Log', 'path' => '/var/log/amazonipn.log'],
        'clientLog' => ['name' => 'Client Log', 'path' => '/var/log/paywithamazon.log']
    ];

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * DeveloperLogs constructor.
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        UrlInterface $urlBuilder,
        $data = []
    ) {
        $this->directoryList = $directoryList;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/logs.phtml');
        }
        return $this;
    }

    /**
     * Render log list
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Renders string as an html element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Returns markup for developer log field.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getLinks()
    {
        $links = $this->getLogFiles();

        if ($links) {
            $output = '';

            foreach ($links as $link) {
                $output .= '<a href="' . $link['link'] . '">' . $link['name'] . '</a><br />';
            }

            return $output;
        }
        return __('No logs are currently available.');
    }

    /**
     * Get list of available log files.
     *
     * @return array
     */
    private function getLogFiles()
    {
        $links = [];

        $path = $this->directoryList->getPath(DirectoryList::ROOT);

        foreach (self::LOGS as $name => $data) {
            $filePath = $data['path'];

            $exists = file_exists($path . $filePath);

            if ($exists) {
                $links[] = ['link' => $this->urlBuilder->getUrl(self::DOWNLOAD_PATH . '/' . $name), 'name' => $data['name']];
            }
        }

        return $links;
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxSyncUrl()
    {
        return $this->getUrl('amazon_core/system_config/amazonlogs');
    }
}
