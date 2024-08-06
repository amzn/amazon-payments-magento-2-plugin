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

class PromoFontSize implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('14px'),
                'value' => '14',
            ],
            [
                'label' => __('16px'),
                'value' => '16',
            ],
            [
                'label' => __('18px'),
                'value' => '18',
            ],
            [
                'label' => __('20px'),
                'value' => '20',
            ]
        ];
    }
}
