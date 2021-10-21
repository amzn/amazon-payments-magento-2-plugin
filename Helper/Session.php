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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class Session
{
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
     * Session constructor.
     * @param CustomerSession $session
     * @param EventManagerInterface $eventManager
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CustomerSession $session,
        EventManagerInterface $eventManager,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdConverter
    ) {
        $this->session      = $session;
        $this->checkoutSession = $checkoutSession;
        $this->eventManager = $eventManager;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdConverter = $maskedQuoteIdConverter;
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
     * For compatibility with customer_customer_authenticated event dispatched from standard login controller.
     * The observers are also attached to this with the exception of password related ones.
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
     * @param AmazonCustomerInterface $amazonCustomer
     * @return void
     */
    public function setAmazonCustomer(AmazonCustomerInterface $amazonCustomer)
    {
        $this->session->setAmazonCustomer($amazonCustomer);
    }

    /**
     * @return void
     */
    public function clearAmazonCustomer()
    {
        $this->session->unsAmazonCustomer();
    }

    /**
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
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Load quote from provided masked quote ID or falls back to loading from the session
     * @param $cartId null|string
     * @return false|CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteFromIdOrSession($cartId = null)
    {
        try {
            if (empty($cartId)) {
                $quote = $this->session->getQuote();
            } else {
                $quoteId = $this->maskedQuoteIdConverter->execute($cartId);
                $quote = $this->cartRepository->get($quoteId);
            }
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return $quote;
    }
}
