<?php

namespace Amazon\Pay\Test\Api;

class AddressEndpointTest extends EndpointTestSetup
{
    public const PATH = '/V1/amazon-spc/v1/cart/{cartId}/address';

    public function testCartIdNotFound()
    {
        $cartId = self::NON_EXISTENT_CART_ID;
        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $cartId, self::PATH);

        $this->expectExceptionMessage('InvalidCartId');
        $this->expectExceptionCode(404);

        $this->_webApiCall($serviceInfo);
    }

    public function testCheckoutSessionIdNotValid()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'cart_details' => [
                'checkout_session_id' => 123456
            ]
        ];

        $this->expectExceptionMessage('BadRequest');
        $this->expectExceptionCode(400);

        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testValidCheckoutSessionId()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'cart_details' => [
                'checkout_session_id' => $this->checkoutSessionId
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString($this->checkoutSessionId, json_encode($response));
        $this->assertStringContainsString('cart_details', json_encode($response));
        $this->assertStringContainsString('total_charge_amount', json_encode($response));
    }
}
