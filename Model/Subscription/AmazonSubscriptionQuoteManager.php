<?php

namespace Amazon\Pay\Model\Subscription;

class AmazonSubscriptionQuoteManager
{   
	public function hasSubscription($quote) 
	{
		return false;
	} 

	public function getFrequencyUnit($item) 
	{
		return '';
	} 

	public function getFrequencyCount($item) 
	{
		return 0;
	} 
}