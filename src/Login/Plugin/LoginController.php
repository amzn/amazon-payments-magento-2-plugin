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
namespace Amazon\Login\Plugin;

use Magento\Customer\Controller\Account\Login;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\Controller\ResultInterface;

class LoginController
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Url
     */
    private $url;

    public function __construct(Session $session, Url $url)
    {
        $this->session = $session;
        $this->url     = $url;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Login $login, ResultInterface $result)
    {
        $this->session->setAfterAmazonAuthUrl($this->url->getAccountUrl());

        return $result;
    }
}
