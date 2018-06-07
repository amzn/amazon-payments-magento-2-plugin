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
namespace Amazon\Login\Controller\Login;

use Magento\Framework\App\Action\Action;
use Amazon\Core\Helper\Data as AmazonCoreHelper;
use Amazon\Login\Model\Validator\AccessTokenRequestValidator;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;

/**
 * Class Guest
 * @package Amazon\Login\Controller\Login
 */
class Guest extends Action
{

    /**
     * @var AmazonCoreHelper
     */
    protected $amazonCoreHelper;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var AccessTokenRequestValidator
     */
    protected $accessTokenRequestValidator;

    /**
     * Guest constructor.
     * @param Context $context
     * @param AmazonCoreHelper $amazonCoreHelper
     * @param Url $customerUrl
     * @param AccessTokenRequestValidator $accessTokenRequestValidator
     */
    public function __construct(
        Context $context,
        AmazonCoreHelper $amazonCoreHelper,
        Url $customerUrl,
        AccessTokenRequestValidator $accessTokenRequestValidator
    ) {
        $this->amazonCoreHelper            = $amazonCoreHelper;
        $this->customerUrl                 = $customerUrl;
        $this->accessTokenRequestValidator = $accessTokenRequestValidator;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->isValidToken()) {
            return $this->getRedirectLogin();
        }

        return $this->_redirect('checkout');
    }

    /**
     * @return string
     */
    protected function getRedirectLogin()
    {
        return $this->_redirect($this->customerUrl->getLoginUrl());
    }

    /**
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    protected function isValidToken()
    {
        return $this->accessTokenRequestValidator->isValid($this->getRequest());
    }
}
