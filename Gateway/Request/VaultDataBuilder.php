<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amazon\Pay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class VaultDataBuilder implements BuilderInterface
{
    
    public function build(array $buildSubject): array
    {
        return [];
    }
}
