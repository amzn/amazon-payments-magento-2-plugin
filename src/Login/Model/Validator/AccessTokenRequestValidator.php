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
namespace Amazon\Login\Model\Validator;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * @deprecated As of February 2021, this Legacy Amazon Pay plugin has been
 * deprecated, in favor of a newer Amazon Pay version available through GitHub
 * and Magento Marketplace. Please download the new plugin for automatic
 * updates and to continue providing your customers with a seamless checkout
 * experience. Please see https://pay.amazon.com/help/E32AAQBC2FY42HS for details
 * and installation instructions.
 */
class AccessTokenRequestValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function isValid($request)
    {
        if (!$request instanceof RequestInterface) {
            throw new \InvalidArgumentException('Provided value must be of type ' . RequestInterface::class);
        }

        return $this->hasAccessToken($request) && !$this->hasError($request);
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function hasError(RequestInterface $request)
    {
        return !empty($request->getParam('error'));
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function hasAccessToken(RequestInterface $request)
    {
        return !empty($request->getParam('access_token'));
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function isDeniedAccessError(RequestInterface $request)
    {
        return $this->hasError($request) && $request->getParam('error') === 'access_denied';
    }
}
