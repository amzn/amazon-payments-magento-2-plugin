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

namespace Amazon\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Amazon\Payment\Gateway\Http\Client\Client;
use Amazon\Payment\Domain\AmazonConstraint;

class ConstraintValidator extends AbstractValidator
{

    /**
     * Performs validation of result code
     *
     * @param  array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $validationSubject['response'];

        if (isset($response['constraints']) && $response['constraints']) {
            $constraint = $response['constraints'][0];
            return $this->createResult(
                false,
                [$this->getConstraint($constraint)]
            );
        }

        // if no constraints found, continue to other validators for more specific errors
        return $this->createResult(
            true,
            ['status' => isset($response['status']) ? $response['status'] : __('No constraints detected.')]
        );
    }

    /**
     * @param AmazonConstraint $constraint
     * @return string
     */
    private function getConstraint(AmazonConstraint $constraint)
    {
        return $constraint->getId();
    }
}
