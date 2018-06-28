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
use Bex\Behat\Magento2InitExtension\Fixtures\MagentoConfigManager;
use Fixtures\Product as ProductFixture;

class ConfigContext implements SnippetAcceptingContext
{
    /**
     * @var MagentoConfigManager
     */
    private $configManager;

    private $hasConfigChanges = false;

    /**
     * @var ProductFixture
     */
    private $productFixture;

    /**
     * @Given IPN is disabled
     */
    public function ipnIsDisabled()
    {
        $this->changeConfig('payment/amazon_payment/update_mechanism', 'polling');
    }

    public function __construct()
    {
        $this->configManager = new MagentoConfigManager;
        $this->productFixture = new ProductFixture;
    }

    /**
     * @Given Login with Amazon is disabled
     */
    public function loginWithAmazonIsDisabled()
    {
        $this->changeConfig('payment/amazon_payment/lwa_enabled', '0');
    }

    /**
     * @Given orders are charged for at order placement
     */
    public function ordersAreChargedForAtOrderPlacement()
    {
        $this->changeConfig('payment/amazon_payment/payment_action', 'authorize_capture');
    }

    /**
     * @Given orders are authorized asynchronously
     */
    public function ordersAreAuthorizedAsynchronously()
    {
        $this->changeConfig('payment/amazon_payment/authorization_mode', 'asynchronous');
    }

    /**
     * @Given orders are authorized synchronously then asynchronously
     */
    public function ordersAreAuthorizedSynchronouslyThenAsynchronously()
    {
        $this->changeConfig('payment/amazon_payment/authorization_mode', 'synchronous_possible');
    }

    /**
     * @Given IPN is enabled
     */
    public function ipnIsEnabled()
    {
        $this->changeConfig('payment/amazon_payment/update_mechanism', 'instant');
    }

    public function changeConfig($path, $value, $scopeType = 'default', $scopeCode = null)
    {
        $this->configManager->changeConfigs(
            [
                [
                    'path'       => $path,
                    'value'      => $value,
                    'scope_type' => $scopeType,
                    'scope_code' => $scopeCode
                ]
            ]
        );

        $this->hasConfigChanges = true;
    }

    /**
     * @AfterScenario
     */
    public function revertConfig()
    {
        if ($this->hasConfigChanges) {
            $this->configManager->revertAllConfig();
            $this->hasConfigChanges = false;
        }
    }

    /**
     * @Given the blacklist term validation is turned on
     */
    public function theBlacklistTermValidationIsTurnedOn()
    {
        $this->changeConfig('payment/amazon_payment/packstation_terms_validation_enabled', 1);
    }

    /**
     * @Given Amazon address contains black listed terms
     */
    public function amazonAddressContainsBlackListedTerms()
    {
        $this->changeConfig('payment/amazon_payment/packstation_terms', implode(',', range('a', 'z')));
    }

    /**
     * @Given Product SKU :productSku belongs to an excluded category
     */
    public function productSkuBelongsToAnExcludedCategory($productSku)
    {
        $product = $this->productFixture->create(['sku' => $productSku]);

        $productCategories = $product->getCategoryIds();

        if (empty($productCategories)) {
            throw new \RuntimeException(
                "Product $productSku has no associated categories."
            );
        }

        $this->changeConfig(
            'payment/amazon_payment/excluded_categories',
            implode(',', $productCategories)
        );
    }
}
