<?php

namespace Amazon\Pay\Test\Api;

class TaxAndShippingEndpointTest extends EndpointTestSetup
{
    const PATH = '/V1/cart/{cartId}/calculate-tax-and-shipping';

    public function testCartIdNotFound()
    {
        $cartId = self::NON_EXISTENT_CART_ID;
        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $cartId, self::PATH);

        $this->expectExceptionMessage('No such entity with cartId = '. $cartId);

        $this->_webApiCall($serviceInfo);
    }

    public function testUpdatingItemQty()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'cartDetails' => [
                [
                    'id' => $this->createdCartItemId,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString('"quantity":2', json_encode($response));
    }

    public function testUpdatingAddressMissingRegion()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'shippingDetails' => [
                'zipcode' => '10001',
                'country' => 'US'
            ]
        ];

        $this->expectExceptionMessage('Region, Zip Code, and Country are required.');

        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testUpdatingAddressMissingZipcode()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'shippingDetails' => [
                'region' => 'NY',
                'country' => 'US'
            ]
        ];

        $this->expectExceptionMessage('Region, Zip Code, and Country are required.');

        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testUpdatingAddressMissingCountry()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'shippingDetails' => [
                'region' => 'NY',
                'zipcode' => '10001',
            ]
        ];

        $this->expectExceptionMessage('Region, Zip Code, and Country are required.');

        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testUpdatingAddress()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'shippingDetails' => [
                'street' => [
                    '100 Spring St'
                ],
                'city' => 'New York',
                'region' => 'NY',
                'zipcode' => '10001',
                'country' => 'US'
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString('"deliveryZipCode":"10001"', json_encode($response));
    }

    public function testSelectShippingMethod()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'shippingDetails' => [
                'street' => [
                    '100 Spring St'
                ],
                'city' => 'New York',
                'region' => 'NY',
                'zipcode' => '10001',
                'country' => 'US',
                'shipping_method' => 'freeshipping_freeshipping'
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString('"amount":0,"selected":true', json_encode($response));
    }

    public function testSelectNotAvailableShippingMethod()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'shippingDetails' => [
                'street' => [
                    '100 Spring St'
                ],
                'city' => 'New York',
                'region' => 'NY',
                'zipcode' => '10001',
                'country' => 'US',
                'shipping_method' => 'fake_method'
            ]
        ];

        $this->expectExceptionMessage('Carrier with such method not found: %1, %2');

        $this->_webApiCall($serviceInfo, $requestData);
    }
}
