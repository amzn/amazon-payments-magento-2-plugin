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

namespace Amazon\PayV2\Api\Data;

interface CheckoutSessionInterface
{
    const KEY_ID = 'id';
    const KEY_SESSION_ID = 'session_id';
    const KEY_QUOTE_ID = 'quote_id';
    const KEY_IS_ACTIVE = 'is_active';
    const KEY_CREATED_AT = 'created_at';
    const KEY_CANCELED_AT = 'canceled_at';
    const KEY_UPDATED_AT = 'updated_at';
    const KEY_COMPLETED_AT = 'completed_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getSessionId();

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @param int $value
     * @return $this
     */
    public function setQuoteId($value);

    /**
     * @return bool
     */
    public function getIsActive();

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @return string|null
     */
    public function getCanceledAt();

    /**
     * @return $this
     */
    public function cancel();

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @return $this
     */
    public function setUpdated();

    /**
     * @return string|null
     */
    public function getCompletedAt();

    /**
     * @return $this
     */
    public function complete();
}
