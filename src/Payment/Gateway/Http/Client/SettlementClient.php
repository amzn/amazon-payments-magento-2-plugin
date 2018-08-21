<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

namespace Amazon\Payment\Gateway\Http\Client;

use Amazon\Core\Exception\AmazonServiceUnavailableException;

/**
 * Class SettlementClient
 * Amazon Pay capture client
 */
class SettlementClient extends AbstractClient
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $response = [];

        // check to see if authorization is still valid
        if ($this->adapter->checkAuthorizationStatus($data)) {
            $captureData = [
                'amazon_authorization_id' => $data['amazon_authorization_id'],
                'capture_amount' => $data['capture_amount'],
                'currency_code' => $data['currency_code'],
                'capture_reference_id' => $data['amazon_order_reference_id'] . '-C' . time()
            ];

            $response = $this->adapter->completeCapture($captureData, $data['store_id']);
        } else {
            // if invalid - reauthorize and capture
            $captureData = [
                'amazon_order_reference_id' => $data['amazon_order_reference_id'],
                'amount' => $data['capture_amount'],
                'currency_code' => $data['currency_code'],
                'seller_order_id' => $data['seller_order_id'],
                'store_name' => $data['store_name'],
                'custom_information' => $data['custom_information'],
                'platform_id' => $data['platform_id']
            ];
            $response = $this->adapter->authorize($data, true);
            $response['reauthorized'] = true;
        }

        return $response;
    }
}
