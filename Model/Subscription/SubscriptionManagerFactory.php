<?php 
namespace Amazon\Pay\Model\Subscription;

class SubscriptionManagerFactory
{
    protected $moduleManager;

    protected $objectManager;

    protected $subscriptionManagerPool;


    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $subscriptionManagerPool = []

    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->subscriptionManagerPool = $subscriptionManagerPool;
    }

    public function initialize(array $data = [])
    {
        $manager = false;
        foreach($this->subscriptionManagerPool as $vendor => $subscriptionManager) {
            if ($vendor != 'default') {
                if ($this->moduleManager->isEnabled($subscriptionManager['module_name'])) {
                    $manager = $this->objectManager->create($subscriptionManager['module_manager'], $data);
                    foreach($subscriptionManager['module_classes'] as $name => $instance) {
                        $manager->{$name} =  $this->objectManager->create($instance);
                    }
                }
            }
        }

        if (!$manager) {
            $moduleManager = $this->subscriptionManagerPool['default']['module_manager'];
            $manager = $this->objectManager->create($moduleManager, $data);
        }
        return $manager;
    }
}