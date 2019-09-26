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
     * Gets Payment configuration status.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }
}
