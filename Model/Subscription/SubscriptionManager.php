<?php
namespace Amazon\Pay\Model\Subscription;

class SubscriptionManager
{

	protected $manager;

	protected $methods;

	public function __construct(
        \Amazon\Pay\Model\Subscription\SubscriptionManagerFactory $subscriptionFactory
    ) {
        $this->manager = $subscriptionFactory->create();
        $this->methods = $subscriptionFactory->getMethods();
    }

	protected function execute($method, $data = null)
	{
		if (isset($this->methods[$method])) {
			$instanceMethod = $this->methods[$method];
			return $this->manager->$instanceMethod($data);
		}

		return false;
	}
}