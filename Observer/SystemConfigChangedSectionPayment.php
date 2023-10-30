<?php

namespace Amazon\Pay\Observer;

use Amazon\Pay\Model\Spc\AuthTokens;
use Magento\Framework\App\MutableScopeConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class SystemConfigChangedSectionPayment implements ObserverInterface
{
    /**
     * @var MutableScopeConfig
     */
    protected $mutableScopeConfig;

    /**
     * @var AuthTokens
     */
    protected $authTokens;

    /**
     * @param MutableScopeConfig $mutableScopeConfig
     * @param AuthTokens $authTokens
     */
    public function __construct(
        MutableScopeConfig $mutableScopeConfig,
        AuthTokens $authTokens
    )
    {
        $this->mutableScopeConfig = $mutableScopeConfig;
        $this->authTokens = $authTokens;
    }

    /**
     * Sync SPC tokens on AP changes
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function execute(Observer $observer)
    {
        $store = $observer->getStore();
        $changedPaths = $observer->getChangedPaths();

        // Check that Amazon changes are included
        if (in_array('payment/amazon_payment_v2/private_key', $changedPaths)) {
            // Check if SPC is enabled or being enabled
            if ($this->mutableScopeConfig->isSetFlag('payment/amazon_payment_v2/spc_enabled', ScopeInterface::SCOPE_STORE, $store ?: 0)
            ) {
                $this->authTokens->createOrRenewAndSendTokens();
            }
        }
    }
}
