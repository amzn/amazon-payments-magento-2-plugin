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

namespace Amazon\Pay\Model\Config\Form;

use Magento\Framework\View\Element\Html\Select;
use Amazon\Pay\Helper\Alexa as AlexaHelper;

class AmazonCarrierRenderer extends Select
{
    /**
     * @var AlexaHelper
     */
    private $alexaHelper;

    /**
     * @param AlexaHelper $alexaHelper
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        AlexaHelper $alexaHelper,
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->alexaHelper = $alexaHelper;
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        $options = [];
        $carriers = $this->alexaHelper->getDeliveryCarriers();
        foreach ($carriers as $carrier) {
            $options[] = ['label' => $carrier['title'], 'value' => $carrier['code']];
        }
        return $options;
    }
}
