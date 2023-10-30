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

use Magento\Vault\Model\PaymentTokenRepository as TokenRepository;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use ParadoxLabs\Subscriptions\Model\Source\Status;
use Amazon\Pay\Helper\SubscriptionHelper;
use Amazon\Pay\Gateway\Config\Config;
use Amazon\Pay\Model\Subscription\SubscriptionManager;
use Amazon\Pay\Model\Subscription\AmazonSubscriptionManager;

class PaymentTokenRepository
{
    /**
     * @var SubscriptionHelper
     */
    private $helper;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param SubscriptionHelper $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(
        SubscriptionHelper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriptionManager $subscriptionManager
    ) {
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * Handle subscription canceled due to payment token deletion
     *
     * @param TokenRepository $tokenRepository
     * @param callable $proceed
     * @param PaymentTokenInterface $paymentToken
     * @return true
     */
    public function aroundDelete(
        TokenRepository $tokenRepository,
        callable $proceed,
        PaymentTokenInterface $paymentToken
    ) {
        if (!($this->subscriptionManager instanceof AmazonSubscriptionManager)) {
            if ($paymentToken->getPaymentMethodCode() === Config::CODE) {
                // Cancel associated AP subscriptions
                $subscriptionsPaidWithToken = $this->helper->getSubscriptionsPaidWithToken($paymentToken);

                $this->helper->cancelToken(reset($subscriptionsPaidWithToken)->getQuote(), $paymentToken);
                foreach ($subscriptionsPaidWithToken as $amazonSubscription) {
                    $amazonSubscription->setStatus(
                        Status::STATUS_CANCELED,
                        'Subscription canceled due to payment token deletion'
                    );
                    $this->subscriptionManager->save($amazonSubscription);
                }

                return true;
            }
        }

        return $proceed($paymentToken);
    }
}
