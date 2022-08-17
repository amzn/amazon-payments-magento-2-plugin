<?php

namespace Amazon\Pay\Model\Subscription;

use Amazon\Pay\Model\AmazonConfig;
use Magento\Checkout\Model\ConfigProviderInterface;

class TokenConfigProvider implements ConfigProviderInterface
{
    /**
     * @var AmazonConfig
     */
    private $amazonConfig;

    /**
     * @param AmazonConfig $amazonConfig
     */
    public function __construct($amazonConfig)
    {
        $this->amazonConfig = $amazonConfig;
    }

    public function getIcon()
    {
        return $this->amazonConfig->getAmazonIcon();
    }

    public function getConfig()
    {
        return [];
    }
}
