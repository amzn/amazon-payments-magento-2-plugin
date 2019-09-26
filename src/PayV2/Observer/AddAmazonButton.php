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
namespace Amazon\PayV2\Observer;

use Magento\Framework\Event\Observer;

class AddAmazonButton implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amazon\PayV2\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Amazon\Payment\Helper\Shortcut\Factory
     */
    private $shortcutFactory;

    /**
     * AddAmazonButton constructor.
     * @param \Amazon\PayV2\Model\AmazonConfig $amazonConfig
     * @param \Amazon\Payment\Helper\Shortcut\Factory $shortcutFactory
     */
    public function __construct(
        \Amazon\PayV2\Model\AmazonConfig $amazonConfig,
        \Amazon\Payment\Helper\Shortcut\Factory $shortcutFactory
    ) {
        $this->amazonConfig = $amazonConfig;
        $this->shortcutFactory = $shortcutFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        if ($this->amazonConfig->isEnabled() && $this->amazonConfig->isCurrentCurrencySupportedByAmazon()) {
            $params = [
                'shortcutValidator' => $this->shortcutFactory->create($observer->getEvent()->getCheckoutSession()),
            ];
            $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();

            /** @var \Magento\Framework\View\Element\Template $shortcut */
            $shortcut = $shortcutButtons->getLayout()->createBlock(
                \Amazon\PayV2\Block\Minicart\Button::class,
                '',
                $params
            );

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
