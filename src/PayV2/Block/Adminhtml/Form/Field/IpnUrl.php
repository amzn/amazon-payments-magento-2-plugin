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
namespace Amazon\PayV2\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;

class IpnUrl extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Render element value
     *
     * @param                                         \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return                                        string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _renderValue(AbstractElement $element)
    {
        $store = $this->_storeManager->getDefaultStoreView();
        $valueReturn = '';

        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
        if ($baseUrl) {
            $value       = $baseUrl . 'amazon_payv2/payment/ipn/';
            $valueReturn = "<div>".$this->escapeHtml($value)."</div>";
        }

        $html = '<td class="value">';
        $html .= $valueReturn;
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';

        return $html;
    }

    /**
     * Render element value
     *
     * @param                                         \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return                                        string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _renderInheritCheckbox(AbstractElement $element)
    {
        return '<td class="use-default"></td>';
    }
}
