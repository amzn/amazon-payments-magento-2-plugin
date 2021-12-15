<?php
namespace Amazon\Pay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Amazon\Pay\Model\AmazonConfig;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'amazon_payment_v2';

    const VAULT_CODE = 'amazon_payment_v2_vault';    

    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigProvider constructor.
     * @param AmazonConfig $config
     */
    public function __construct(
        AmazonConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {

        $config = [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->isActive()
                ],
                self::VAULT_CODE => [
                    'isActive' => $this->config->isVaultEnabled()
                ]
            ]
        ];

        return $config;
    }
}
