<?php
namespace Amazon\Pay\Model\Subscription;

interface SubscriptionManagerInterface
{
	public function hasSubscription($quote);
    public function getFrequencyUnit($item);
    public function getFrequencyCount($item);
    public function isSubscription($item);
    public function cancel($order, $subscription = false); 
}