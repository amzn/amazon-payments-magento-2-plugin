<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amazon\Payment\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'amazon_payment';
    
    const KEY_ACTIVE = 'active';

    /**
     * @var \Amazon\Core\Model\AmazonConfig
     */
    protected $amazonConfig;

    /**
     * @param \Amazon\Core\Model\AmazonConfig $amazonConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Amazon\Core\Model\AmazonConfig $amazonConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->amazonConfig = $amazonConfig;
        parent::__construct($scopeConfig, self::CODE);
    }

    /**
     * @param int|null $storeId
     * @return boolean
     */
    protected function canCapturePartial($storeId = null)
    {
        $region = $this->amazonConfig->getPaymentRegion(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        switch ($region) {
            case 'de':
            case 'uk':
                $result = false;
                break;
            default:
                $result = parent::getValue('can_capture_partial', $storeId);
                break;
        }
        return $result;
    }

    /**
     * Gets Payment configuration status.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        switch ($field) {
            case 'can_capture_partial':
                $result = $this->canCapturePartial($storeId);
                break;
            default:
                $result = parent::getValue($field, $storeId);
                break;
        }
        return $result;
    }
}
