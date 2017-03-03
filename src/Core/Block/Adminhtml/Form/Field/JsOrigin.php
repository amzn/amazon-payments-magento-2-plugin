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
namespace Amazon\Core\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as BaseField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;
use Zend\Uri\UriFactory;

class JsOrigin extends BaseField
{
    protected function _renderValue(AbstractElement $element)
    {
        $stores = $this->_storeManager->getStores();
        $valueReturn = '';

        foreach ($stores as $store) {
            $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
            if ($baseUrl) {
                $uri   = UriFactory::factory($baseUrl);
                $value = $uri->getScheme() . '://' . $uri->getHost();
                $valueReturn .= "<div>".$value."</div>";
            }
        }

        return '<td class="value">' . $valueReturn . '</td>';
    }

    protected function _renderInheritCheckbox(AbstractElement $element)
    {
        return '<td class="use-default"></td>';
    }
}
