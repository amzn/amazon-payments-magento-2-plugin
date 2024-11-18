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

use Magento\Framework\Data\Form\Element\AbstractElement;

class Note extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    protected $amazonConfig;

    /**
     * Note constructor
     *
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Amazon\Pay\Model\AmazonConfig $amazonConfig,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->amazonConfig = $amazonConfig;
        parent::__construct($context, $data);
    }

    /**
     * Render scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }

    /**
     * Render note
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _renderValue(AbstractElement $element)
    {
        $html = '<td class="value">';
        if (!$this->amazonConfig->getPrivateKey() || !$this->amazonConfig->getPublicKeyId()) {
            $html .= __('Log in to Seller Central. Navigate to Integration Central, to access the below required keys');
        }
        $html .= '</td>';
        return $html;
    }
}
