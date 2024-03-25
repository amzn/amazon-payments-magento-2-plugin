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
namespace Amazon\Pay\Helper;

use Amazon\Pay\Api\Data\AmazonCustomerInterface;
use Amazon\Pay\Domain\ValidationCredentials;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class Session
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CustomerSession
     */
    private $session;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdConverter;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * Session constructor.
     *
     * @param CartManagementInterface $cartManagement
     * @param CustomerSession $session
     * @param EventManagerInterface $eventManager
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdConverter
     * @param UserContextInterface $userContext
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        CustomerSession $session,
        EventManagerInterface $eventManager,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdConverter,
        UserContextInterface $userContext
    ) {
        $this->cartManagement = $cartManagement;
        $this->session      = $session;
        $this->checkoutSession = $checkoutSession;
        $this->eventManager = $eventManager;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdConverter = $maskedQuoteIdConverter;
        $this->userContext = $userContext;
    }

    /**
     * Login customer by data
     *
     * @param CustomerInterface $customerData
     */
    public function login(CustomerInterface $customerData)
    {
        $this->dispatchAuthenticationEvent();

        if ($customerData->getId() != $this->session->getId() || !$this->session->isLoggedIn()) {
            $this->session->setCustomerDataAsLoggedIn($customerData);
            $this->session->regenerateId();
            $this->checkoutSession->loadCustomerQuote();
        }
    }

    /**
     * Login customer by id
     *
     * @param integer $customerId
     */
    public function loginById($customerId)
    {
        $this->dispatchAuthenticationEvent();
        $this->session->loginById($customerId);
        $this->session->regenerateId();
    }

    /**
     * Dispatch 'amazon_customer_authenticated' event
     *
     * For compatibility with customer_customer_authenticated event dispatched from standard login controller.
     * The observers are also attached to this with the exception of password related ones.
     *
     * @return void
     */
    protected function dispatchAuthenticationEvent()
    {
        $this->eventManager->dispatch('amazon_customer_authenticated');
    }

    /**
     * Set validation credentials in session
     *
     * @param ValidationCredentials $credentials
     */
    public function setValidationCredentials(ValidationCredentials $credentials)
    {
        $this->session->setAmazonValidationCredentials($credentials);
    }

    /**
     * Get validation credentials from session
     *
     * @return ValidationCredentials|null
     */
    public function getValidationCredentials()
    {
        $credentials = $this->session->getAmazonValidationCredentials();

        return ($credentials) ?: null;
    }

    /**
     * Check if Magento account is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * Check if user is logged in to Amazon
     *
     * @return bool
     */
    public function isAmazonLoggedIn()
    {
        return $this->session->getIsAmazonLoggedIn();
    }

    /**
     * Set session flag indicating whether user signed in through Amazon
     *
     * @param bool $isLoggedIn
     * @return void
     */
    public function setIsAmazonLoggedIn($isLoggedIn)
    {
        if ($isLoggedIn) {
            $this->session->setIsAmazonLoggedIn(true);
        } else {
            $this->session->unsIsAmazonLoggedIn();
        }
    }

    /**
     * Associate Amazon customer with session
     *
     * @param AmazonCustomerInterface $amazonCustomer
     * @return void
     */
    public function setAmazonCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        $this->session->setAmazonCustomer($amazonCustomer);
    }

    /**
     * Disassociate Amazon customer with session
     *
     * @return void
     */
    public function clearAmazonCustomer()
    {
        $this->session->unsAmazonCustomer();
    }

    /**
     * Get Amazon customer information from session
     *
     * @return AmazonCustomerInterface|null
     */
    public function getAmazonCustomer()
    {
        $amazonCustomer = $this->session->getAmazonCustomer();

        if ($amazonCustomer && (!$amazonCustomer instanceof AmazonCustomerInterface)) {
            $this->clearAmazonCustomer();
            $amazonCustomer = null;
        }

        return $amazonCustomer;
    }

    /**
     * Get quote from Magento checkout session
     *
     * @return CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Get quote from cart ID or user session
     *
     * @param mixed $cartId
     * @return false|CartInterface
     */
    public function getQuoteFromIdOrSession($cartId = null)
    {
        try {
            // the intention of is_numeric is to filter out any potential unwanted requests trying to guess quote ids
            // we only really want to utilize masked ids here unless retrieved elsewhere
            if (empty($cartId) || is_numeric($cartId)) {
                $quote = $this->getQuote();
                if (!$quote || !$quote->getId()) {
                    // here we'll check the user context for any available cart data before moving on
                    $userContextCartId = $this->getCartIdViaUserContext();
                    if ($userContextCartId !== null) {
                        return $this->cartRepository->get($userContextCartId);
                    }
                }
                return $quote;
            }
            $quoteId = $this->maskedQuoteIdConverter->execute($cartId);
            return $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException | LocalizedException $e) {
            return false;
        }
    }

    /**
     * Get cart from customer ID
     *
     * @return int|null
     */
    public function getCartIdViaUserContext()
    {
        try {
            $customerId = $this->userContext->getUserId();

            /** @var CartInterface */
            $cart = $this->cartManagement->getCartForCustomer($customerId);
            if ($cart) {
                return $cart->getId();
            }
            return null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
