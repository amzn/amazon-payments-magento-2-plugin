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

namespace Amazon\Pay\Model\Config\Source;

class ButtonColor implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Create option array from button color choices
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [[
            'label' => __('Gold'),
            'value' => 'Gold',
        ], [
            'label' => __('Light Gray'),
            'value' => 'LightGray',
        ], [
            'label' => __('Dark Gray'),
            'value' => 'DarkGray',
        ]];
    }
}
