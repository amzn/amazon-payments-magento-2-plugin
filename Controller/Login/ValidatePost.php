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
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Encryption\Encryptor;

class ValidatePost extends Action implements HttpPostActionInterface
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
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * ValidatePost constructor.
     *
     * @param Context                      $context
     * @param Session                      $session
     * @param AccountRedirect              $accountRedirect
     * @param CustomerRegistry             $customerRegistry
     * @param Encryptor                    $encryptor
     * @param customerLinkManagement       $customerLinkManagement
     * @param FormKeyValidator|null        $formKeyValidator
     * @param AuthenticationInterface|null $authentication
     */
    public function __construct(
        Context $context,
        Session $session,
        AccountRedirect $accountRedirect,
        CustomerRegistry $customerRegistry,
        Encryptor $encryptor,
        CustomerLinkManagementInterface $customerLinkManagement,
        ?FormKeyValidator $formKeyValidator = null,
        ?AuthenticationInterface $authentication = null
    ) {
        parent::__construct($context);

        $this->session                = $session;
        $this->accountRedirect        = $accountRedirect;
        $this->customerRegistry       = $customerRegistry;
        $this->encryptor              = $encryptor;
        $this->customerLinkManagement = $customerLinkManagement;
        $this->formKeyValidator       = $formKeyValidator
            ?: ObjectManager::getInstance()->get(FormKeyValidator::class);
        $this->authentication         = $authentication
            ?: ObjectManager::getInstance()->get(AuthenticationInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired, please reload the page and try again.')
            );
            return $this->_redirect($this->_url->getRouteUrl('*/*/validate'));
        }

        $credentials = $this->session->getValidationCredentials();

        if (null !== $credentials && $credentials instanceof ValidationCredentials) {
            $customerId = $credentials->getCustomerId();

            // Check the password of the account being linked, honouring Magento's lockout rules
            if ($this->authentication->isLocked($customerId)) {
                $this->messageManager->addErrorMessage(__(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                ));
                return $this->_redirect($this->_url->getRouteUrl('*/*/validate'));
            }

            $password = (string)$this->getRequest()->getParam('password');
            $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
            $hash = $customerSecure->getPasswordHash() ?? '';

            if (!$this->encryptor->validateHash($password, $hash)) {
                $this->authentication->processAuthenticationFailure($customerId);
                $this->messageManager->addErrorMessage(__('The password supplied was incorrect'));
                return $this->_redirect($this->_url->getRouteUrl('*/*/validate'));
            }

            $this->session->clearValidationCredentials();
            $this->customerLinkManagement->updateLink($customerId, $credentials->getAmazonId());
            $this->session->loginById($customerId);
        }

        return $this->accountRedirect->getRedirect();
    }
}
