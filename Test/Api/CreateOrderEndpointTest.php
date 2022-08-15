<?php

namespace Amazon\Pay\Test\Api;

class CreateOrderEndpointTest extends EndpointTestSetup
{
    const PATH = '/V1/cart/{cartId}/create-order';

    protected function tearDown(): void
    {
        $this->upStockOnProduct();

        parent::tearDown();
    }

    public function testCartIdNotFound()
    {
        $cartId = self::NON_EXISTENT_CART_ID;
        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $cartId, self::PATH);

        $this->expectExceptionMessage('No such entity with cartId = '. $cartId);

        $requestData = ['checkoutSessionId' => 'ABCD1234'];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testCheckoutSessionIdNotProvided()
    {
        $this->createCart();
        $this->addItemToCart();
        $this->setAddressShippingMethodEmail();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $this->expectExceptionMessage('\"%fieldName\" is required. Enter and try again.');

        $this->_webApiCall($serviceInfo);
    }

    public function testCreateOrderItemNotEnoughStock()
    {
        $this->createCart();
        $this->addItemToCart(20);
        $this->setAddressShippingMethodEmail();
        $this->setLowStockOnProduct();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = ['checkoutSessionId' => 'ABCD1234'];

        $this->expectExceptionMessage('The requested qty is not available');

        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testCreateOrder()
    {
        $this->createCart();
        $this->addItemToCart();
        $this->setAddressShippingMethodEmail();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = ['checkoutSessionId' => 'ABCD1234'];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString('orderId', json_encode($response));
    }
}
