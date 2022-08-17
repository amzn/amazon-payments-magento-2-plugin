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

use Amazon\Pay\Model\Adapter\AmazonPayAdapter;
use ParadoxLabs\Subscriptions\Model\SubscriptionRepository;
use ParadoxLabs\Subscriptions\Model\Subscription;
use ParadoxLabs\Subscriptions\Model\Source\Status;
use Magento\Quote\Model\Quote;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

class SubscriptionHelper
{
    /**
     * @var AmazonPayAdapter
     */
    private $amazonAdapter;

    /**
     * @param PaymentTokenRepository
     */
    private $paymentTokenRepository;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @param AmazonPayAdapter $amazonAdapter
     * @param PaymentTokenRepository $paymentTokenRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        AmazonPayAdapter $amazonAdapter,
        PaymentTokenRepository $paymentTokenRepository,
        SubscriptionRepository $subscriptionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->amazonAdapter = $amazonAdapter;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Close charge permission and deactivate token when subscription is canceled.
     *
     * @param Quote $quote
     * @param PaymentToken $token
     * @return void
     */
    public function cancelToken(Quote $quote, PaymentToken $token)
    {
        $this->amazonAdapter->closeChargePermission(
            $quote->getStoreId(),
            $token->getGatewayToken(),
            'Closed due to deleted payment method or cancellation of subscription by customer.'
        );

        $token->setIsActive(false);
        $token->setIsVisible(false);
        $this->paymentTokenRepository->save($token);
    }

    /**
     * Get Amazon payment descriptor from stored token
     *
     * @param PaymentToken $token
     * @return string
     */
    public function getTokenPaymentDescriptor(PaymentToken $token)
    {
        return json_decode($token->getTokenDetails())
            ->paymentPreferences[0]
            ->paymentDescriptor;
    }

    /**
     * Compare frequencies of recurring metadata
     *
     * @param array $first
     * @param array $second
     * @return boolean
     */
    public function hasShorterFrequency(array $first, array $second)
    {
        $unitMap = [
            'day'   => 1,
            'week'  => 2,
            'month' => 3,
            'year'  => 4
        ];

        if ($unitMap[strtolower($first['unit'])] < $unitMap[strtolower($second['unit'])]) {
            return true;
        } elseif ($unitMap[strtolower($first['unit'])] == $unitMap[strtolower($second['unit'])]) {
            return $first['value'] > $second['value'];
        }

        return false;
    }

    /**
     * Get a list of all subscriptions whose payment method is $token
     *
     * @param PaymentToken $token
     * @return Subscription[]
     */
    public function getSubscriptionsPaidWithToken(PaymentToken $token)
    {
        $publicHash = $token->getPublicHash();
        $customerId = $token->getCustomerId();

        $customerFilter = $this->filterBuilder
            ->setField('customer_id')
            ->setValue($customerId)
            ->setConditionType('eq')
            ->create();

        $activeFilter = $this->filterBuilder
            ->setField('status')
            ->setValue(Status::STATUS_ACTIVE)
            ->setConditionType('eq')
            ->create();

        $pausedFilter = $this->filterBuilder
            ->setField('status')
            ->setValue(Status::STATUS_PAUSED)
            ->setConditionType('eq')
            ->create();

        $completeFilter = $this->filterBuilder
            ->setField('status')
            ->setValue(Status::STATUS_COMPLETE)
            ->setConditionType('eq')
            ->create();

        $customerFilterGroup = $this->filterGroupBuilder->setFilters([$customerFilter])->create();
        $statusFilterGroup = $this->filterGroupBuilder->setFilters([
            $activeFilter, $pausedFilter, $completeFilter
        ])->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups([$customerFilterGroup, $statusFilterGroup])
            ->create();

        $activeSubscriptions = $this->subscriptionRepository->getList($searchCriteria)
            ->getItems();
        $subscriptionsPaidWithToken = array_filter($activeSubscriptions, function ($subscription) use ($publicHash) {
            return $subscription->getQuote()
                ->getPayment()
                ->getAdditionalInformation('public_hash') === $publicHash;
        });

        return $subscriptionsPaidWithToken;
    }
}
