<?php

namespace Amazon\Pay\Helper\Spc;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UniqueId
{
    const UNIQUE_ID_CONFIG_PATH = 'payment/amazon_pay/unique_id';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @return mixed|string
     */
    public function getUniqueId()
    {
        $value = $this->scopeConfig->getValue(self::UNIQUE_ID_CONFIG_PATH);

        if (empty($value)) {
            $value = $this->createAndSaveUniqueId();
        }

        return $value;
    }

    /**
     * @return string
     */
    protected function createAndSaveUniqueId()
    {
        $uniqueId = uniqid();

        $this->configWriter->save(self::UNIQUE_ID_CONFIG_PATH, $uniqueId);

        return $uniqueId;
    }
}
