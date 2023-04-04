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

class RestrictCategories extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Catalog\Ui\Component\Product\Form\Categories\Options
     */
    private $categoryOptions;

    /**
     * RestrictCategories constructor
     *
     * @param \Magento\Catalog\Ui\Component\Product\Form\Categories\Options $categoryOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Ui\Component\Product\Form\Categories\Options $categoryOptions,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryOptions = $categoryOptions;
    }

    /**
     * Retrieve data from Restricted Categories field
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return array
     */
    protected function _getElementData(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        parse_str($element->getName(), $result);
        $data = &$result;
        while (is_array($data)) {
            $key = array_keys($data)[0];
            $data = &$data[$key];
        }
        if ($element->getValue()) {
            $data = explode(',', $element->getValue());
        }
        return $result;
    }

    /**
     * Create JS data scope from config element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementDataScope(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        parse_str($element->getName(), $data);
        $scopes = [];
        while (is_array($data)) {
            $key = array_keys($data)[0];
            $scopes[] = $key;
            $data = $data[$key];
        }
        return implode('.', $scopes);
    }

    /**
     * Render restricted categories element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $elementData = $this->_getElementData($element);
        $elementDataScope = $this->_getElementDataScope($element);
        return '<div data-bind="scope: \'restrict_categories\'">' .
            '<!-- ko template: getTemplate() --><!-- /ko --></div><script type="text/x-magento-init">' .
            json_encode([
            '*' => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'restrict_categories' => [
                            'component' => 'uiCollection',
                            'children' => [
                                'input' => [
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'ui/form/element/hidden',
                                    'provider' => 'restrict_categories.data_source',
                                    'dataScope' => 'data.' . $elementDataScope,
                                ],
                                'element' => [
                                    'component' => 'Magento_Catalog/js/components/new-category',
                                    'template' => 'ui/grid/filters/elements/ui-select',
                                    'filterOptions' => true,
                                    'chipsEnabled' => true,
                                    'levelsVisibility' => 1,
                                    'provider' => 'restrict_categories.data_source',
                                    'dataScope' => 'data.' . $elementDataScope,
                                    'options' => $this->categoryOptions->toOptionArray(),
                                ],
                                'data_source' => [
                                    'component' => 'Magento_Ui/js/form/provider',
                                    'data' => $elementData,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]) . '</script>';
    }
}
