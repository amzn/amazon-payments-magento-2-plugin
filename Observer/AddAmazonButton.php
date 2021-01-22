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
namespace Amazon\Pay\Observer;

use Magento\Framework\Event\Observer;

class AddAmazonButton implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amazon\Pay\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * AddAmazonButton constructor.
     * @param \Amazon\Pay\Model\AmazonConfig $amazonConfig
     */
    public function __construct(
        \Amazon\Pay\Model\AmazonConfig $amazonConfig
    ) {
        $this->amazonConfig = $amazonConfig;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        if ($this->amazonConfig->isEnabled()) {
            /** @var \Magento\Framework\View\Element\Template $shortcut */
            $shortcut = $shortcutButtons->getLayout()->createBlock(\Amazon\Pay\Block\Minicart\Button::class);

            $shortcut->setIsInCatalogProduct(
                $observer->getEvent()->getIsCatalogProduct()
            )->setShowOrPosition(
                $observer->getEvent()->getOrPosition()
            );

            $shortcut->setIsCart(get_class($shortcutButtons) == \Magento\Checkout\Block\QuoteShortcutButtons::class);

            $shortcutButtons->addShortcut($shortcut);
        }
    }
}
