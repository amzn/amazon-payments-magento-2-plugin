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
namespace Context\Data;

use Behat\Behat\Context\SnippetAcceptingContext;
use Fixtures\Product as ProductFixture;
use Magento\Catalog\Api\Data\ProductInterface;

class ProductContext implements SnippetAcceptingContext
{
    /**
     * @var ProductFixture
     */
    private $productFixture;

    public function __construct()
    {
        $this->productFixture = new ProductFixture;
    }

    /**
     * @Given there is a product with sku :sku
     */
    public function thereIsAProductWithSku($sku)
    {
        $this->productFixture->create([ProductInterface::SKU => $sku]);
    }
}
