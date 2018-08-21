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
use Amazon\Payment\Domain\AmazonConstraint;

/**
 * Class AuthorizationValidator
 * Validates authorization calls during gateway payment
 */
class AuthorizationValidator extends AbstractValidator
{

    /**
     * Performs validation of result code
     *
     * @param  array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $messages = [];

        $response = $validationSubject['response'];

        if (isset($response['sandbox']) && $response['sandbox']) {
            $bits = explode(':', $response['sandbox']);
            $messages[] = $bits[count($bits) - 1];
            return $this->createResult(false, $messages);
        }

        if (isset($response['status']) && $response['status']) {
            return $this->createResult(
                true,
                ['status' => $response['status']]
            );
        }

        if (isset($response['response_code']) && $response['response_code']) {
            $messages[] = $response['response_code'];
        } elseif (isset($response['constraints']) && $response['constraints']) {
            $messages[] = $this->getConstraint($response['constraints'][0]);
        }

        return $this->createResult(false, $messages);

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
