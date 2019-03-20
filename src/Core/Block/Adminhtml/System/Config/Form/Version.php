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
namespace Amazon\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Amazon\Core\Model\AmazonConfig;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * Version constructor.
     * @param AmazonConfig $amazonConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        AmazonConfig $amazonConfig,
        array $data = []
    ) {
        $this->amazonConfig = $amazonConfig;
        parent::__construct($context, $data);
    }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $version = $this->amazonConfig->getVersion();
        if (!$version) {
            $version = __('--');
        }
        $output = '<div style="background-color:#eee;padding:1em;border:1px solid #ddd;">';
        $output .= __('Module version') . ': ' . $version;
        $output .= "</div>";
        return $output;
    }
}
