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
namespace Amazon\Pay\Model\Validator;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Validator\AbstractValidator;

class AccessTokenRequestValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function isValid($request)
    {
        if (!$request instanceof RequestInterface) {
            throw new \InvalidArgumentException('Provided value must be of type ' . RequestInterface::class);
        }

        return $this->hasBuyerToken($request) && !$this->hasError($request);
    }

    /**
     * True if request indicates there was an error
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function hasError(RequestInterface $request)
    {
        return !empty($request->getParam('error'));
    }

    /**
     * True if request contains Amazon buyer token
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function hasBuyerToken(RequestInterface $request)
    {
        return !empty($request->getParam('buyerToken'));
    }

    /**
     * True if error was due to access restriction
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function isDeniedAccessError(RequestInterface $request)
    {
        return $this->hasError($request) && $request->getParam('error') === 'access_denied';
    }
}
