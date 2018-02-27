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

class AmazonConstraint
{
    const PAYMENT_METHOD_NOT_ALLOWED_ID = 'PaymentMethodNotAllowed';
    const PAYMENT_PLAN_NOT_SET_ID = 'PaymentPlanNotSet';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * AmazonConstraint constructor.
     *
     * @param string $id
     * @param string $description
     */
    public function __construct($id, $description)
    {
        $this->id          = $id;
        $this->description = $description;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getErrorMessage()
    {
        switch ($this->getId()) {
            case static::PAYMENT_METHOD_NOT_ALLOWED_ID:
            case static::PAYMENT_PLAN_NOT_SET_ID:
                return 'Please select a payment method.';
            default:
                return 'Amazon could not process your request.';
        }
    }
}
