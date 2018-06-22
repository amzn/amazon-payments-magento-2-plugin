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
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction. Constraints found.')]
            );
        }

        return $this->createResult(
            true,
            ['status' => $response['status']]
        );
    }

}
