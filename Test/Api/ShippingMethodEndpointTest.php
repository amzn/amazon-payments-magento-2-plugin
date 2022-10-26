<?php

namespace Amazon\Pay\Test\Api;

class ShippingMethodEndpointTest extends EndpointTestSetup
{
    const PATH = '/V1/amazon-spc/v1/cart/{cartId}/shipping-method';
    const SERVICE_INFO = [
        'rest' => [
            'resourcePath' => '',
            'httpMethod' => 'POST'
        ]
    ];
    const REQUEST_DATA = ['couponCode' => 'CouponCode7'];

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

    public function testSettingShippingMethod()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'cart_details' => [
                'delivery_options' => [
                    [
                        'shipping_method' => [
                            'shipping_method_code' => 'flatrate_flatrate'
                        ]
                    ]
                ],
                'checkout_session_id' => $this->checkoutSessionId
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString('"shipping_method_code":"flatrate_flatrate"},"shipping_estimate":[],"is_default":true', json_encode($response));
    }

    public function testUpdateShippingMethod()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'cart_details' => [
                'delivery_options' => [
                    [
                        'shipping_method' => [
                            'shipping_method_code' => 'freeshipping_freeshipping'
                        ]
                    ]
                ],
                'checkout_session_id' => $this->checkoutSessionId
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString('"shipping_method_code":"flatrate_flatrate"},"shipping_estimate":[],"is_default":false', json_encode($response));
        $this->assertStringContainsString('"shipping_method_code":"freeshipping_freeshipping"},"shipping_estimate":[],"is_default":true', json_encode($response));
    }
}
