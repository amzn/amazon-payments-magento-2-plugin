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
namespace Amazon\Pay\Plugin;

use Amazon\Pay\Gateway\Config\Config;
use ParadoxLabs\Subscriptions\Model\Source\Status;

class SubscriptionRepository
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var \Amazon\Pay\Helper\SubscriptionHelper
     */
    private $helper;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement,
        \Amazon\Pay\Helper\SubscriptionHelper $helper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave($SubscriptionRepository, $subscription)
    {
        $status = $subscription->getStatus();
        if ($status === Status::STATUS_CANCELED || $status === Status::STATUS_PAYMENT_FAILED) {
            $quoteId = $subscription->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $payment = $quote->getPayment();

            if ($payment->getMethod() === Config::VAULT_CODE) {
                $customerId = $payment->getAdditionalInformation('customer_id');
                $publicHash = $payment->getAdditionalInformation('public_hash');
                $token = $this->paymentTokenManagement->getByPublicHash($publicHash, $customerId);

                $subscriptionsPaidWithToken = $this->helper->getSubscriptionsPaidWithToken($token);
                if (empty($subscriptionsPaidWithToken)) {
                    $this->helper->cancelToken($quote, $token);
                }
            }
        }

        return $subscription;
    }
}
