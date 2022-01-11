<?php 
namespace Amazon\Pay\Model\Subscription;

class AmazonSubscriptionManager implements SubscriptionManagerInterface
{

    public function hasSubscription($quote)
    {
    	return false;
    }

    public function getFrequencyUnit($item)
    {
    	return false;
    }

    public function getFrequencyCount($item)
    {
    	return 0;
    }

    public function isSubscription($item)
    {
    	return false;
    }

    public function cancel($order, $subscription = false) 
	{
		return false;
	} 

}