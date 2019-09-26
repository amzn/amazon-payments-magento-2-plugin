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

namespace Amazon\PayV2\Gateway\Http\Client;

/**
 * Class VoidClient
 */
class VoidClient extends AbstractClient
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $response = $this->adapter->cancelCharge($data['store_id'], $data['charge_id']);
        return $response;
    }
}
