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

use Amazon\Pay\Domain\ValidationCredentials;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Quote\Api\Data\CartInterface;

class Checkout extends \Amazon\Pay\Controller\Login
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $checkoutSessionId = $this->getRequest()->getParam('amazonCheckoutSessionId');
        $maskedQuoteId = $this->getRequest()->getParam('magentoCartId');
        if ($checkoutSessionId == '') {
            return $this->_redirect('checkout/cart');
        }

        try {
            $checkoutSession = $this->amazonAdapter->getCheckoutSession(
                $this->storeManager->getStore()->getId(),
                $checkoutSessionId
            );

            if (!$this->amazonConfig->isLwaEnabled()) {
                $userInfo = $checkoutSession['buyer'];
                if ($userInfo && isset($userInfo['email'])) {
                    $userEmail = $userInfo['email'];
                    $quote = $this->session->getQuoteFromIdOrSession($maskedQuoteId);

                    if ($quote) {
                        $quote->setCustomerEmail($userEmail);
                        $quote->save();
                    }
                }
            } else {
                $buyerInfo = $checkoutSession['buyer'];
                $amazonCustomer = $this->getAmazonCustomer($buyerInfo);
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
                } else {
                    $this->logger->error('Amazon buyerId is empty');
                }
            }
        } catch (ValidatorException $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_eventManager->dispatch('amazon_login_authorize_validation_error', ['exception' => $e]);
            $this->_eventManager->dispatch('amazon_login_authorize_error', ['exception' => $e]);
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(__('An error occurred while matching your Amazon account with your store account. '));
            $this->_eventManager->dispatch('amazon_login_authorize_error', ['exception' => $e]);
        }

        $checkoutUrl = $this->amazonConfig->getCheckoutReviewUrlPath();
        return $this->_redirect($checkoutUrl, ['_query' => ['amazonCheckoutSessionId' => $checkoutSessionId]]);
    }
}
