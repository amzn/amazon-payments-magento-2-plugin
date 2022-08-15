<?php

namespace Amazon\Pay\Test\Api;

class ApplyCouponEndpointTest extends EndpointTestSetup
{
    const SERVICE_INFO = [
        'rest' => [
            'resourcePath' => '',
            'httpMethod' => 'POST'
        ]
    ];

    const PATH = '/V1/cart/{cartId}/apply-coupon';

    const REQUEST_DATA = ['couponCode' => 'CouponCode7'];

    public function testCartIdNotFound()
    {
        $cartId = self::NON_EXISTENT_CART_ID;
        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $cartId, self::PATH);

        $this->expectExceptionMessage('No such entity with cartId = '. $cartId);

        $this->_webApiCall($serviceInfo, self::REQUEST_DATA);
    }

    public function testMissingCouponCode()
    {
        $cartId = 1000;
        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $cartId, self::PATH);

        $this->expectExceptionMessage('\"%fieldName\" is required. Enter and try again.');

        $this->_webApiCall($serviceInfo);
    }

    public function testCouponDoesNotApplyNoItems()
    {
        $this->createCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $this->expectExceptionMessage('Coupon code is not applicable.');

        $this->_webApiCall($serviceInfo, self::REQUEST_DATA);
    }

    public function testCouponCodeDoesNotExist()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $this->expectExceptionMessage('Coupon code is not applicable.');

        $this->_webApiCall($serviceInfo, ['couponCode' => 'code2000']);
    }

    public function testApplyCouponToCart()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $response = $this->_webApiCall($serviceInfo, self::REQUEST_DATA);

        $this->assertStringContainsString(self::REQUEST_DATA['couponCode'], json_encode($response));
    }

    public function testUpdateCouponCode()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $response = $this->_webApiCall($serviceInfo, ['couponCode' => 'CouponCode8']);

        $this->assertStringContainsString('CouponCode8', json_encode($response));
    }

    public function testRemoveCouponCode()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $response = $this->_webApiCall($serviceInfo, ['couponCode' => '']);

        $this->assertStringContainsString('"couponCode":""', json_encode($response));
    }
}
