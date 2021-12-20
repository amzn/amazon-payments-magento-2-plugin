<?php

namespace Amazon\Pay\Model\Subscription;

class SubscriptionItemManager extends SubscriptionManager
{
	public function __construct(
        \Amazon\Pay\Model\Subscription\SubscriptionItemManagerFactory $subscriptionFactory
    ) {
        parent::__construct($subscriptionFactory);
    }

	public function getFrequencyUnit($item)
	{
		$unit = $this->execute('get_frecuency_unit', $item);
		if ($unit) {
			$unit = ucfirst($unit);
		}
		return $unit;
	}

	public function getFrequencyCount($item)
	{
		return $this->execute('get_frequency_count', $item);
	}

	public function isSubscription($item)
	{
		return $this->execute('is_subscription', $item);	
	}
}