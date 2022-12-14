<?php

namespace Amazon\Pay\Cron;

use Amazon\Pay\Model\Spc\AuthTokens;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SpcCheckAndSyncTokens
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AuthTokens
     */
    protected $authTokens;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param AuthTokens $authTokens
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        AuthTokens $authTokens
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->authTokens = $authTokens;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function execute()
    {
        // disabling cron for now
        return;

        $lastSync = $this->scopeConfig->getValue(AuthTokens::LAST_SYNC_CONFIG_PATH);
        $lastSync = \DateTime::createFromFormat('Y-m-d H:i:s', $lastSync);

        $twoWeeksAgo = new \DateTime('-2 weeks');

        // Sync if no last sync has been saved, or two weeks have passed
        if (!$lastSync || $lastSync <= $twoWeeksAgo) {
            $this->authTokens->createOrRenewAndSendTokens();
        }
    }
}
