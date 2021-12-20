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

class SubscriptionRepository
{
   
    /**
     * @var \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter
     */
    private $amazonAdapter;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    private $quoteRepository;


    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;


    /**
     * @var \Magento\Vault\Api\PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    
    public function __construct(
        \Amazon\Pay\Model\Adapter\AmazonPayAdapter $amazonAdapter,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Vault\Api\PaymentTokenManagementInterface $paymentTokenManagement,
        \Magento\Vault\Api\PaymentTokenRepositoryInterface $paymentTokenRepository

    ) {
        $this->amazonAdapter = $amazonAdapter;
        $this->quoteRepository = $quoteRepository;
        $this->paymentTokenManagement = $paymentTokenManagement;      
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave($SubscriptionRepository, $subscription)
    {

        if ($subscription->getStatus() == 'canceled') {
            $quoteId = $subscription->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $payment = $quote->getPayment();
            $publicHash = $payment->getAdditionalInformation('public_hash');
            $customerId = $payment->getAdditionalInformation('customer_id');
            $token = $this->paymentTokenManagement->getByPublicHash($publicHash, $customerId);

            if ($token) {
                $this->amazonAdapter->closeChargePermission(
                    $quote->getStoreId(),
                    $token->getGatewayToken(),
                    'Canceled due to cancellation of subscription by the customer.'
                );

                $token->setIsActive(false);
                $this->paymentTokenRepository->save($token);
            }
        }
        
        return $subscription;
    }
}
