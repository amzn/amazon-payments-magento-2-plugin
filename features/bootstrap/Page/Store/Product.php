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
namespace Page\Store;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Page\AmazonLoginTrait;
use Page\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use \Magento\Framework\App\ObjectManager;

class Product extends Page
{
    use PageTrait, AmazonLoginTrait;

    private $path = '/catalog/product/view/id/{id}';

    private $elements
        = [
            'add-to-cart'     => ['css' => '#product-addtocart-button'],
            'success-message' => ['css' => '.message-success'],
            'open-amazon-login' => ['css' => '#OffAmazonPaymentsWidgets0'],
            'amazon-login'      => ['css' => 'button'],
        ];

    /**
     * @param int $productId
     */
    public function openWithProductId($productId)
    {
        $this->open(['id' => (int) $productId]);
    }

    /**
     * @param string $sku
     */
    public function openWithProductSku($sku)
    {
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $this->open(['id' => $productRepository->get($sku)->getId()]);
    }

    public function addToBasket()
    {
        $this->getElement('add-to-cart')->click();
        $this->waitForElement('success-message');
    }

    /**
     * @return bool
     */
    public function pwaButtonIsVisible()
    {
        try {
            return $this->getElementWithWait('open-amazon-login', 30000)->isVisible();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function pwaButtonIsVisibleNoWait()
    {
        try {
            return $this->getElement('open-amazon-login')->isVisible();
        } catch (\Exception $e) {
            return false;
        }
    }
}
