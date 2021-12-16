<?php 
namespace Amazon\Pay\Model\Subscription;

class SubscriptionManagerFactory
{
    protected $moduleManager;

    protected $objectManager;

    protected $subscriptionManagerPool;

    protected $instanceName = false;

    protected $methods = [];

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $subscriptionManagerPool = []

    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->subscriptionManagerPool = $subscriptionManagerPool;
    }

    public function create(array $data = [])
    {
        foreach($this->subscriptionManagerPool as $subscriptionManager) {
            
            if ($this->moduleManager->isEnabled($subscriptionManager['module_name'])) {
                
                $this->instanceName = $subscriptionManager['module_class'];

                foreach ($subscriptionManager['methods'] as $method => $moduleMethod) {
                    $this->methods[$method] = $moduleMethod;
                }
            } 
        }
        
        if (!$this->instanceName) {
            $this->createDefaults();
        }

        return $this->objectManager->create($this->instanceName, $data);
    }

    public function getMethods()
    {
        return $this->methods;
    }
}