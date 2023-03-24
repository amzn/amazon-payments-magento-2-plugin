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

class CarrierRenderer extends Select
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * CarrierRenderer constructor
     *
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shippingConfig = $shippingConfig;
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
     * @param string $value
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

    /**
     * Get carriers for tracking number dropdown
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $options = [];
        $carriers = $this->shippingConfig->getAllCarriers();
        foreach ($carriers as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $options[] = ['label' => $carrier->getConfigData('title'), 'value' => $code];
            }
        }
        return $options;
    }
}
