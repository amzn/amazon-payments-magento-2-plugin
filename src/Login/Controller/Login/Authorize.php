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

use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Login\Domain\ValidationCredentials;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NotFoundException;
use Zend_Validate;

class Authorize extends \Amazon\Login\Controller\Login
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->amazonCoreHelper->isLwaEnabled()) {
            throw new NotFoundException(__('Action is not available'));
        }

        if (!$this->isValidToken()) {
            return $this->getRedirectLogin();
        }

        try {
            $amazonCustomer = $this->getAmazonCustomer();
            if ($amazonCustomer) {
                $processed = $this->processAmazonCustomer($amazonCustomer);

                if ($processed instanceof ValidationCredentials) {
                    $this->session->setValidationCredentials($processed);
                    $this->session->setAmazonCustomer($amazonCustomer);
                    return $this->_redirect($this->_url->getRouteUrl('*/*/validate'));
                } else {
                    $this->session->login($processed);
                }
            }
        } catch (ValidatorException $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_eventManager->dispatch('amazon_login_authorize_validation_error', ['exception' => $e]);
            $this->_eventManager->dispatch('amazon_login_authorize_error', ['exception' => $e]);
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('Error processing Amazon Login'));
            $this->_eventManager->dispatch('amazon_login_authorize_error', ['exception' => $e]);
        }

        return $this->getRedirectAccount();
    }

    protected function processAmazonCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        $customerData = $this->matcher->match($amazonCustomer);

        if (null === $customerData) {
            return $this->createCustomer($amazonCustomer);
        }

        if ($amazonCustomer->getId() != $customerData->getExtensionAttributes()->getAmazonId()) {
            if (! $this->session->isLoggedIn()) {
                return new ValidationCredentials($customerData->getId(), $amazonCustomer->getId());
            }

            $this->customerLinkManagement->updateLink($customerData->getId(), $amazonCustomer->getId());
        }

        return $customerData;
    }

    protected function createCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        if (! Zend_Validate::is($amazonCustomer->getEmail(), 'EmailAddress')) {
            throw new ValidatorException(__('the email address for your Amazon account is invalid'));
        }

        $customerData = $this->customerLinkManagement->create($amazonCustomer);
        $this->customerLinkManagement->updateLink($customerData->getId(), $amazonCustomer->getId());

        return $customerData;
    }
}
