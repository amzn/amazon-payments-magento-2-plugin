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
namespace Fixtures\Helper\Product;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use \Magento\Framework\App\ObjectManager;

class StockDataProvider implements ProductDataProvider
{
    const KEY_STOCK_ITEM = StockStatusInterface::STOCK_ITEM;
    const KEY_QTY = StockStatusInterface::QTY;
    const CODE_QUANTITY_AND_STOCK_STATUS = 'quantity_and_stock_status';

    /**
     * @var int
     */
    private $qty = 100;

    /**
     * @var bool
     */
    private $isInStock = true;

    /**
     * @var bool
     */
    private $useConfigManageStock = false;

    /**
     * @var bool
     */
    private $manageStock = true;

    /**
     * @param ProductInterface $product
     */
    public function addDataToProduct(ProductInterface $product)
    {
        $this->initProduct($product);

        $product->setCustomAttribute(self::CODE_QUANTITY_AND_STOCK_STATUS, [true, $this->qty]);

        /** @var ProductExtensionInterface $extensionAttr */
        $extensionAttr = $product->getExtensionAttributes();

        /** @var StockItemInterface $stockItem */
        $stockItem = ObjectManager::getInstance()->get(StockItemInterface::class);

        $stockItem->setQty($this->qty);
        $stockItem->setIsInStock($this->isInStock);
        $stockItem->setUseConfigManageStock($this->useConfigManageStock);
        $stockItem->setManageStock($this->manageStock);

        $extensionAttr->setStockItem($stockItem);

        $product->setExtensionAttributes($extensionAttr);
    }

    /**
     * @param ProductInterface $product
     */
    protected function initProduct(ProductInterface $product)
    {
        $extensionAttrs = $product->getData(ProductDataProvider::KEY_EXTENSION_ATTRIBUTES);

        if (!$extensionAttrs instanceof ProductExtensionInterface) {
            /** @var ProductExtensionInterface $extensionAttr */
            $extensionAttr = ObjectManager::getInstance()->get(ProductExtensionFactory::class)->create();
            $product->setExtensionAttributes($extensionAttr);
        }
    }

    /**
     * @param boolean $manageStock
     */
    public function setManageStock($manageStock)
    {
        $this->manageStock = (bool) $manageStock;

        if ($this->manageStock) {
            $this->setUseConfigManageStock(false);
        }
    }

    /**
     * @param boolean $useConfigManageStock
     */
    public function setUseConfigManageStock($useConfigManageStock)
    {
        $this->useConfigManageStock = (bool) $useConfigManageStock;

        if ($this->useConfigManageStock) {
            $this->setManageStock(false);
        }
    }

    /**
     * @param boolean $isInStock
     */
    public function setIsInStock($isInStock)
    {
        $this->isInStock = (bool) $isInStock;
    }

    /**
     * @param int|float $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }
}
