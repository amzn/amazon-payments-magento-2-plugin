<?php
namespace Amazon\Pay\Model\Subscription;

class SubscriptionManager
{

	protected $manager;


	public function __construct(
        \Amazon\Pay\Model\Subscription\SubscriptionManagerFactory $subscriptionFactory
    ) {
        $this->manager = $subscriptionFactory->initialize();
    }

	public function hasSubscription($quote)
	{
        return $this->manager->hasSubscription($quote);
    }
    public function getFrequencyUnit($item)
    {
        $unit = $this->manager->getFrequencyUnit($item);
        if ($unit) {
            $unit = ucfirst($unit);
        }
        return $unit;
    }

    public function getFrequencyCount($item)
    {
        return $this->manager->getFrequencyCount($item);
    }

    public function isSubscription($item)
    {
        return $this->manager->isSubscription($item);
    }

    public function cancel($order, $subscription = false)
	{
        return $this->manager->cancel($order, $subscription);
	}
}