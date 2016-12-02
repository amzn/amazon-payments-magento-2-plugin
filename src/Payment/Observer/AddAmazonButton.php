<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
namespace Amazon\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Amazon\Core\Helper\Data;

class AddAmazonButton implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $coreHelper;

    /**
     * @param Data $coreHelper
     */
    public function __construct(
        Data $coreHelper
    ) {
        $this->coreHelper = $coreHelper;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        if ($this->coreHelper->isPwaEnabled() && $this->coreHelper->isCurrentCurrencySupportedByAmazon()) {

//            $params = [
//                'shortcutValidator' => $this->shortcutFactory->create($observer->getEvent()->getCheckoutSession()),
//            ];
            $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();

            /** @var \Magento\Framework\View\Element\Template $shortcut */
            $shortcut = $shortcutButtons->getLayout()->createBlock(
                'Amazon\Payment\Block\Minicart\Button',
                '',
                $params
            );

            $shortcut->setIsInCatalogProduct(
                $observer->getEvent()->getIsCatalogProduct()
            )->setShowOrPosition(
                $observer->getEvent()->getOrPosition()
            );
            $shortcutButtons->addShortcut($shortcut);
        }
    }
}
