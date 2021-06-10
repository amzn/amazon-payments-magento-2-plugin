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

use Magento\Backend\Block\Template\Context;

class AutoKeyExchangeConfig extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_layout
            ->createBlock(\Amazon\Pay\Block\Adminhtml\System\Config\AutoKeyExchangeAdmin::class)
            ->setTemplate('Amazon_Pay::system/config/autokeyexchange_admin.phtml')
            ->setCacheable(false)
            ->toHtml();

        return '<div id="row_' . $element->getHtmlId() . '">' . $html . '</div>';
    }
}
