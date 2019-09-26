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

/**
 * Class VoidClient
 * Amazon Pay client for gateway cancel and void
 */
class VoidClient extends AbstractClient
{

    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $store_id = $data['store_id'];
        unset($data['store_id']);

        $response = [
            'status' => false
        ];

        $client = $this->clientFactory->create($store_id);
        $responseParser = $client->cancelOrderReference($data);

        if ($responseParser->response['Status'] == 200) {
            // Gateway expects response to be in form of array
            $response['status'] = true;
        } else {
            $log['error'] = __('VoidClient - Unable to Close/Cancel order - bad status response.');
            $this->logger->debug($log);
        }

        return $response;
    }
}
