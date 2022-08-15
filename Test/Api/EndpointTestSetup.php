<?php

namespace Amazon\Pay\Test\Api;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\TestCase\WebapiAbstract;

class EndpointTestSetup extends WebapiAbstract
{
    const SERVICE_INFO = [
        'rest' => [
            'resourcePath' => '',
            'httpMethod' => 'POST'
        ]
    ];

    const TEST_PRODUCT_SKU = 'product_dynamic_122';

    const NON_EXISTENT_CART_ID = 10000000;

    protected $createdCartId;

    protected $createdCartMaskedId;

    protected $createdCartItemId;

    protected function setUp(): void
    {
        $this->clearInventoryReservations();
        $this->upStockOnProduct();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->clearInventoryReservations();

        parent::tearDown();
    }

    protected function clearInventoryReservations()
    {
        $objectManager = ObjectManager::getInstance();
        $resourceConnection = $objectManager->get(ResourceConnection::class);
        $connection = $resourceConnection->getConnection();
        $table = $connection->getTableName('inventory_reservation');
        $query = "TRUNCATE TABLE ". $table;
        $connection->query($query);
    }

    protected function createCart()
    {
        if (!$this->createdCartId) {
            // create cart
            $serviceInfo = self::SERVICE_INFO;
            $serviceInfo['rest']['resourcePath'] = '/V1/guest-carts';
            $cartMaskedId = $this->_webApiCall($serviceInfo);
            $this->createdCartMaskedId = $cartMaskedId;

            // get cart's db id
            $serviceInfo = self::SERVICE_INFO;
            $serviceInfo['rest']['resourcePath'] = '/V1/guest-carts/' . $cartMaskedId;
            $serviceInfo['rest']['httpMethod'] = 'GET';
            $cart = $this->_webApiCall($serviceInfo);

            $this->createdCartId = $cart['id'];
        }

        return $this->createdCartId;
    }

    protected function addItemToCart($qty = 1, $sku = self::TEST_PRODUCT_SKU)
    {
        if (!$this->createdCartItemId) {
            // add item
            $serviceInfo = self::SERVICE_INFO;
            $serviceInfo['rest']['resourcePath'] = '/V1/guest-carts/' . $this->createdCartMaskedId . '/items';
            $requestData = [
                'cartItem' => [
                    'sku' => $sku,
                    'qty' => $qty
                ]
            ];

            $cartItem = $this->_webApiCall($serviceInfo, $requestData);

            $this->createdCartItemId = $cartItem['item_id'];
        }

        return $this->createdCartItemId;
    }

    protected function setAddressShippingMethodEmail()
    {
        $serviceInfo = self::SERVICE_INFO;
        $path = '/V1/cart/{cartId}/calculate-tax-and-shipping';
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, $path);

        $requestData = [
            'shippingDetails' => [
                'street' => [
                    '100 Spring St'
                ],
                'city' => 'New York',
                'region' => 'NY',
                'zipcode' => '10001',
                'country' => 'US',
                'phone' => '1234567890',
                'shipping_method' => 'freeshipping_freeshipping',
                'email' => 'test@test.com'
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    protected function setLowStockOnProduct()
    {
        // get product's stock item id
        $serviceInfo = self::SERVICE_INFO;
        $path = '/V1/products/'. self::TEST_PRODUCT_SKU;
        $serviceInfo['rest']['resourcePath'] = $path;
        $serviceInfo['rest']['httpMethod'] = 'GET';

        $productResponse = $this->_webApiCall($serviceInfo);
        $stockItemId = $productResponse['extension_attributes']['stock_item']['item_id'];

        // update stock by using sku and stock item id
        $serviceInfo = self::SERVICE_INFO;
        $path .= '/stockItems/'. $stockItemId;
        $serviceInfo['rest']['resourcePath'] = $path;
        $serviceInfo['rest']['httpMethod'] = 'PUT';

        $requestData = [
            'stockItem' => [
                'qty' => 10
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    protected function upStockOnProduct()
    {
        // get product's stock item id
        $serviceInfo = self::SERVICE_INFO;
        $path = '/V1/products/'. self::TEST_PRODUCT_SKU;
        $serviceInfo['rest']['resourcePath'] = $path;
        $serviceInfo['rest']['httpMethod'] = 'GET';

        $productResponse = $this->_webApiCall($serviceInfo);
        $stockItemId = $productResponse['extension_attributes']['stock_item']['item_id'];

        // update stock by using sku and stock item id
        $serviceInfo = self::SERVICE_INFO;
        $path = $path .'/stockItems/'. $stockItemId;
        $serviceInfo['rest']['resourcePath'] = $path;
        $serviceInfo['rest']['httpMethod'] = 'PUT';

        $requestData = [
            'stockItem' => [
                'qty' => 10000
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }
}
