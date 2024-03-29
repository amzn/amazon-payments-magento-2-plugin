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

class AuthorizationMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public const ASYNC = 'asynchronous';
    public const SYNC = 'synchronous';
    public const SYNC_THEN_ASYNC = 'synchronous_possible';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => static::SYNC, 'label' => __('Immediate')],
            ['value' => static::SYNC_THEN_ASYNC, 'label' => __('Automatic')]
        ];
    }
}
