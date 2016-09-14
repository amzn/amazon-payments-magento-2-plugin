<?php
use Magento\Catalog\Api\Data\ProductInterface;

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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Base class for Product Data provider.
 *
 * Data providers populate the received Product with data needed to properly create it using M2 system.
 */
interface ProductDataProvider
{
    const KEY_CUSTOM_ATTRIBUTES = 'custom_attributes';
    const KEY_EXTENSION_ATTRIBUTES = ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY;

    public function addDataToProduct(ProductInterface $product);
}
