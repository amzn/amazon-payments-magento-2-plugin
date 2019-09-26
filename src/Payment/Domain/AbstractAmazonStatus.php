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
namespace Amazon\Payment\Domain;

abstract class AbstractAmazonStatus
{
    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $reasonCode;

    /**
     * AmazonAuthorizationStatus constructor.
     *
     * @param string $state
     * @param string|null $reasonCode
     */
    public function __construct($state, $reasonCode = null)
    {
        $this->state      = $state;
        $this->reasonCode = $reasonCode;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get reason code
     *
     * @return string|null
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }
}
