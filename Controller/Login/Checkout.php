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

use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Amazon\Pay\Domain\ValidationCredentials;
use Magento\Framework\Exception\ValidatorException;
use Zend_Validate;

class Checkout extends \Amazon\Pay\Controller\Login
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $checkoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');
        if ($checkoutSessionId == '') {
            return $this->_redirect('checkout/cart');
        }

        try {
            $checkoutSession = $this->amazonAdapter->getCheckoutSession(
                $this->storeManager->getStore()->getId(),
                $checkoutSessionId
            );

            $this->checkoutSessionManagement->storeCheckoutSession(
                $this->session->getQuote()->getId(),
                $checkoutSessionId
            );

            if (!$this->amazonConfig->isLwaEnabled()) {
                $userInfo = $checkoutSession['buyer'];
                if ($userInfo && isset($userInfo['email'])) {
                    $userEmail = $userInfo['email'];
                    $quote = $this->session->getQuote();

                    if ($quote) {
                        $quote->setCustomerEmail($userEmail);
                        $quote->save();
                    }
                }
            } else {
                $amazonCustomer = $this->createAmazonCustomerFromSession($checkoutSession);
                if ($amazonCustomer) {
                    $processed = $this->processAmazonCustomer($amazonCustomer);

                    if ($processed instanceof ValidationCredentials) {
                        $this->session->setValidationCredentials($processed);
                        $this->session->setAmazonCustomer($amazonCustomer);
                        return $this->_redirect(
                            $this->_url->getUrl(
                                '*/*/validate',
                                ['_query' => ['amazonCheckoutSessionId' => $checkoutSessionId]]
                            )
                        );
                    } elseif (!$this->customerSession->isLoggedIn()) {
                        $this->session->login($processed);
                    }
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

        return $this->_redirect('checkout', ['_query' => ['amazonCheckoutSessionId' => $checkoutSessionId]]);
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

    /**
     * @param $checkoutSession
     * @return array|false
     */
    protected function getAmazonCustomerFromSession($checkoutSession)
    {
        $userInfo = $checkoutSession['buyer'];

        if (is_array($userInfo) && array_key_exists('buyerId', $userInfo)) {
            $data = [
                'id' => $userInfo['buyerId'],
                'email' => $userInfo['email'],
                'name' => $userInfo['name'],
                'country' => $this->amazonConfig->getRegion(),
            ];

            return $data;
        }

        return false;
    }

    /**
     * @param $checkoutSession
     * @return \Amazon\Pay\Domain\AmazonCustomer
     */
    protected function createAmazonCustomerFromSession($checkoutSession)
    {
        $data = $this->getAmazonCustomerFromSession($checkoutSession);

        return $this->amazonCustomerFactory->create($data);
    }
}
