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

namespace Amazon\PayV2\Block\Adminhtml\System\Config\Form;

class Note extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    protected $amazonConfig;

    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->amazonConfig = $amazonConfig;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderScopeLabel($element)
    {
        return '';
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue($element)
    {
        $html = '<td class="value">';
        if ($this->amazonConfig->getPrivateKey() && $this->amazonConfig->getPublicKeyId()) {
            $html .= __('Go to Seller Central to get the keys');
        }
        $html .= '</td>';
        return $html;
    }
}
