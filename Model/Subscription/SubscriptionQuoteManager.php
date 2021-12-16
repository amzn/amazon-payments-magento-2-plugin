<?php

namespace Amazon\Pay\Model\Subscription;

class SubscriptionQuoteManager extends SubscriptionManager
{
	public function __construct(
        \Amazon\Pay\Model\Subscription\SubscriptionQuoteManagerFactory $subscriptionFactory
    ) {
        parent::__construct($subscriptionFactory);
    }

	public function hasSubscription($quote) 
	{
		return $this->execute('has_subscription', $quote);
	} 
}