<?php 
namespace Amazon\Pay\Model\Subscription;

class SubscriptionQuoteManagerFactory extends SubscriptionManagerFactory implements SubscriptionManagerFactoryInterface
{
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $subscriptionManagerPool = []

    ) {
        parent::__construct($moduleManager,$objectManager,$subscriptionManagerPool);
    }

    public function createDefaults()
    {
        $this->instanceName = 'Amazon\Pay\Model\Subscription\AmazonSubscriptionQuoteManager';
        $this->methods = ['has_subscription' => 'hasSubscription'];
    }
}