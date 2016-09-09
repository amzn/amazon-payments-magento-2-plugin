<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License').
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the 'license' file accompanying this file. This file is distributed
 * on an 'AS IS' BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Fixtures\Helper\Product;

use Context\Data\FixtureContext;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\ObjectManager;

class NewCategoryDataProvider implements ProductDataProvider
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var string
     */
    private $categoryName;

    /**
     * @var null|int
     */
    private $categoryParentId;

    /**
     * @var callable
     */
    private $afterCreateCallback;

    /**
     */
    public function __construct()
    {
        $this->categoryFactory    = ObjectManager::getInstance()->get(CategoryFactory::class);
        $this->categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        $this->categoryName       = uniqid('categ_name_' . time());
    }

    /**
     * @param string $categoryName
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = (string) $categoryName;
    }

    /**
     * @param null|int $categoryParentId
     */
    public function setCategoryParentId($categoryParentId)
    {
        $this->categoryParentId = $categoryParentId;
    }

    /**
     * @param callable $afterCreateCallback
     */
    public function setAfterCreateCallback(callable $afterCreateCallback)
    {
        $this->afterCreateCallback = $afterCreateCallback;
    }

    /**
     * @param ProductInterface $product
     */
    public function addDataToProduct(ProductInterface $product)
    {
        $category = $this->categoryFactory->create(['data' => [
            'name' => $this->categoryName,
            'is_active' => true,
            'position' => 2,
            'include_in_menu' => false,
        ]]);

        if ($this->categoryParentId) {
            $category->setParentId($this->categoryParentId);
        }

        $category->setCustomAttributes([
            'display_mode'=> 'PRODUCTS',
            'is_anchor'=> '1',
            'custom_use_parent_settings'=> '0',
            'custom_apply_to_products'=> '0',
            'url_key'=> $this->categoryName,
            'url_path'=> $this->categoryName,
            'automatic_sorting'=> '0',
        ]);

        $this->categoryRepository->save($category);

        if (is_callable($this->afterCreateCallback)) {
            call_user_func($this->afterCreateCallback, $category);
        }

        if ($category->getId()) {
            FixtureContext::trackFixture($category, $this->categoryRepository);

            $categories = (array) $product->getData('category_ids');
            $categories = array_merge($categories, [$category->getId()]);

            $product->setData('category_ids', $categories);
        }
    }
}
