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

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Amazon\Pay\Model\Config\Form\CarrierRenderer;
use Amazon\Pay\Model\Config\Form\AmazonCarrierRenderer;

class CarrierCodes extends AbstractFieldArray
{
    /**
     * @var CarrierRenderer
     */
    private $carrierRenderer;

    /**
     * @var AmazonCarrierRenderer
     */
    private $amazonCarrierRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('carrier', [
            'label' => __('Magento Carrier'),
            'renderer' => $this->getCarrierRenderer()
        ]);

        $this->addColumn('amazon_carrier', [
            'label' => __('Amazon Carrier'),
            'renderer' => $this->getAmazonCarrierRenderer()
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $carrier = $row->getCarrier();
        $amazonCarrier = $row->getAmazonCarrier();

        if ($carrier !== null) {
            $option = 'option_' . $this->getCarrierRenderer()->calcOptionHash($carrier);
            $options[$option] = 'selected="selected"';
        }

        if ($amazonCarrier !== null) {
            $option = 'option_' . $this->getAmazonCarrierRenderer()->calcOptionHash($amazonCarrier);
            $options[$option] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return CarrierRenderer
     * @throws LocalizedException
     */
    private function getCarrierRenderer()
    {
        if (!$this->carrierRenderer) {
            $this->carrierRenderer = $this->getLayout()->createBlock(
                CarrierRenderer::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->carrierRenderer;
    }

    /**
     * @return AmazonCarrierRenderer
     * @throws LocalizedException
     */
    private function getAmazonCarrierRenderer()
    {
        if (!$this->amazonCarrierRenderer) {
            $this->amazonCarrierRenderer = $this->getLayout()->createBlock(
                AmazonCarrierRenderer::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->amazonCarrierRenderer;
    }
}
