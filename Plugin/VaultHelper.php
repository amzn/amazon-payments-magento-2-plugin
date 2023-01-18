<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Pay\Plugin;

use ParadoxLabs\Subscriptions\Helper\Vault;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Amazon\Pay\Gateway\Config\Config;
use Amazon\Pay\Helper\SubscriptionHelper;

class VaultHelper
{
    /**
     * @var SubscriptionHelper
     */
    private $helper;

    /**
     * @param SubscriptionHelper $helper
     */
    public function __construct(SubscriptionHelper $helper)
    {
        $this->helper = $helper;
    }

    public function aroundGetCardLabel(
        Vault $vault,
        callable $proceed,
        PaymentTokenInterface $card
    ) {
        if ($card->getPaymentMethodCode() === Config::CODE) {
            return $this->helper->getTokenPaymentDescriptor($card);
        }

        return $proceed($card);
    }
}
