<?php

/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Amazon\Pay\Block\Adminhtml\System\Config\Form;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Amazon\Pay\Helper\Data
     */
    protected $helper;

    /**
     * Version constructor
     *
     * @param \Amazon\Pay\Helper\Data $helper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Amazon\Pay\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Render version information
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $output = '<div style="background-color:#eee;padding:1em;border:1px solid #ddd;">';
        $output .= __('Module version') . ': ' . $this->helper->getModuleVersion('Amazon_Pay');
        $output .= '</div>';
        return '<div id="row_' . $element->getHtmlId() . '">' . $output . '</div>';
    }
}
