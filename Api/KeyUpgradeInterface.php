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

namespace Amazon\Pay\Api;

/**
 * @api
 */
interface KeyUpgradeInterface
{
    /**
     * Obtain a new Public Key ID for use with V2 of the Amazon Pay API
     *
     * @param string $scopeType
     * @param integer $scopeCode
     * @param string $accessKey
     * @return mixed
     */
    public function getPublicKeyId(
        string $scopeType,
        int $scopeCode,
        string $accessKey
    );

    /**
     * Get public/private keys for module configuration
     *
     * @return array
     */
    public function getKeyPair();

    /**
     * Persist keys in configuration
     *
     * @param string $publicKeyId
     * @param string $scopeType
     * @param int $scopeId
     * @return void
     */
    public function updateKeysInConfig(
        $publicKeyId,
        $scopeType,
        $scopeId
    );
}
