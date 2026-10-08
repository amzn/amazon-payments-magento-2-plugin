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
namespace Amazon\Pay\Model;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Per-session anti-CSRF value carried in the Amazon Sign-in return URL
 */
class SignInState
{
    public const PARAM_NAME = 'amazonSignInState';

    private const SESSION_KEY = 'amazon_sign_in_state';

    /**
     * @var CustomerSession
     */
    private $session;

    /**
     * SignInState constructor
     *
     * @param CustomerSession $session
     */
    public function __construct(CustomerSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the state value for the current session, generating one if needed
     *
     * @return string
     */
    public function get()
    {
        $state = $this->session->getData(self::SESSION_KEY);
        if (!is_string($state) || $state === '') {
            $state = bin2hex(random_bytes(32));
            $this->session->setData(self::SESSION_KEY, $state);
        }

        return $state;
    }

    /**
     * Check a returned state value against the session and consume it, so it can't be replayed
     *
     * @param mixed $state
     * @return bool
     */
    public function validate($state)
    {
        $expected = $this->session->getData(self::SESSION_KEY, true);

        return is_string($expected) && $expected !== ''
            && is_string($state) && hash_equals($expected, $state);
    }
}
