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
use Amazon\Pay\Model\AmazonConfig;
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
     * @var AmazonConfig
     */
    private $amazonConfig;

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
     * @param AmazonConfig|null            $amazonConfig
     */
    public function __construct(
        Context $context,
        Session $session,
        AccountRedirect $accountRedirect,
        CustomerRegistry $customerRegistry,
        Encryptor $encryptor,
        CustomerLinkManagementInterface $customerLinkManagement,
        ?FormKeyValidator $formKeyValidator = null,
        ?AuthenticationInterface $authentication = null,
        ?AmazonConfig $amazonConfig = null
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
        $this->amazonConfig           = $amazonConfig
            ?: ObjectManager::getInstance()->get(AmazonConfig::class);
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
            return $this->redirectToValidate();
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
                return $this->redirectToValidate();
            }

            $password = (string)$this->getRequest()->getParam('password');
            $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
            $hash = $customerSecure->getPasswordHash() ?? '';

            if (!$this->encryptor->validateHash($password, $hash)) {
                $this->authentication->processAuthenticationFailure($customerId);
                $this->messageManager->addErrorMessage(__('The password supplied was incorrect'));
                return $this->redirectToValidate();
            }

            $this->session->clearValidationCredentials();
            $this->customerLinkManagement->updateLink($customerId, $credentials->getAmazonId());
            $this->session->loginById($customerId);

            // Linking started from Amazon checkout, so continue to the checkout review
            if ($checkoutSessionId = $this->getCheckoutSessionId()) {
                return $this->_redirect(
                    $this->amazonConfig->getCheckoutReviewUrlPath(),
                    ['_query' => ['amazonCheckoutSessionId' => $checkoutSessionId]]
                );
            }
        }

        return $this->accountRedirect->getRedirect();
    }

    /**
     * Redirect back to the password confirmation, keeping the Amazon checkout session if there is one
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    private function redirectToValidate()
    {
        $checkoutSessionId = $this->getCheckoutSessionId();
        $query = $checkoutSessionId ? ['_query' => ['amazonCheckoutSessionId' => $checkoutSessionId]] : [];

        return $this->_redirect($this->_url->getUrl('*/*/validate', $query));
    }

    /**
     * Get the Amazon checkout session ID posted with the confirmation, if it looks valid
     *
     * @return string
     */
    private function getCheckoutSessionId()
    {
        $checkoutSessionId = (string)$this->getRequest()->getParam('amazonCheckoutSessionId');

        return preg_match('/^[A-Za-z0-9-]{1,100}$/', $checkoutSessionId) ? $checkoutSessionId : '';
    }
}
