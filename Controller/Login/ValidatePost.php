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
namespace Amazon\Pay\Controller\Login;

use Amazon\Pay\Api\CustomerLinkManagementInterface;
use Amazon\Pay\Domain\ValidationCredentials;
use Amazon\Pay\Helper\Session;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Encryption\Encryptor;

class ValidatePost extends Action
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerLinkManagement
     */
    private $customerLinkManagement;

    /**
     * ValidatePost constructor.
     *
     * @param Context                  $context
     * @param Session                  $session
     * @param AccountRedirect          $accountRedirect
     * @param CustomerRegistry         $customerRegistry
     * @param Encryptor                $encryptor
     * @param customerLinkManagement   $customerLinkManagement
     */
    public function __construct(
        Context $context,
        Session $session,
        AccountRedirect $accountRedirect,
        CustomerRegistry $customerRegistry,
        Encryptor $encryptor,
        CustomerLinkManagementInterface $customerLinkManagement
    ) {
        parent::__construct($context);

        $this->session                = $session;
        $this->accountRedirect        = $accountRedirect;
        $this->customerRegistry       = $customerRegistry;
        $this->encryptor              = $encryptor;
        $this->customerLinkManagement = $customerLinkManagement;
    }

    public function execute()
    {
        $credentials = $this->session->getValidationCredentials();

        if (null !== $credentials && $credentials instanceof ValidationCredentials) {
            $password = $this->getRequest()->getParam('password');
            $customerSecure = $this->customerRegistry->retrieveSecureData($credentials->getCustomerId());
            $hash = $customerSecure->getPasswordHash() ?? '';

            if ($this->encryptor->validateHash($password, $hash)) {
                $this->customerLinkManagement->updateLink($credentials->getCustomerId(), $credentials->getAmazonId());
                $this->session->loginById($credentials->getCustomerId());
            } else {
                $this->messageManager->addErrorMessage('The password supplied was incorrect');
                return $this->_redirect($this->_url->getRouteUrl('*/*/validate'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}
