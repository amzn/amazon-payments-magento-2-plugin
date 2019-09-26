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

namespace Amazon\PayV2\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class GeneralResponseValidator
 * Validates responses from gateway
 */
class GeneralResponseValidator extends AbstractValidator
{

    /**
     * Performs validation of result code
     *
     * @param  array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $errorMessages = [];
        $errorCodes = [];

        $response = $validationSubject['response'];

        if (isset($response['statusDetails'])) {
            if (!empty($response['statusDetails']['reasonCode'])) {
                $isValid = false;
                $errorCodes[] = $response['statusDetails']['reasonCode'];
            }
            if (!empty($response['statusDetails']['reasonDescription'])) {
                $isValid = false;
                $errorMessages[] = $response['statusDetails']['reasonDescription'];
            }
        }

        if (!empty($response['reasonCode'])) {
            $isValid = false;
            $errorCodes[] = $response['reasonCode'];
        }
        if (!empty($response['reasonDescription'])) {
            $isValid = false;
            $errorMessages[] = $response['reasonDescription'];
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
