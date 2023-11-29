<?php

namespace Amazon\Pay\Test\Api;

class CouponEndpointTest extends EndpointTestSetup
{
    public const PATH = '/V1/amazon-spc/v1/cart/{cartId}/coupon';

    public const SERVICE_INFO = [
        'rest' => [
            'resourcePath' => '',
            'httpMethod' => 'POST'
        ]
    ];

    public const COUPONS = [['coupon_code' => 'CouponCode0']];

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

    public function testCouponCodeDoesNotExist()
    {
        $this->createCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);
        $requestData = [
            'cart_details' => [
                'coupons' => [
                    [
                        'coupon_code' => 'code1000'
                    ]
                ],
                'checkout_session_id' => $this->checkoutSessionId
            ]
        ];

        $this->expectExceptionMessage('CouponNotApplicable');
        $this->expectExceptionCode(400);

        $this->_webApiCall($serviceInfo, $requestData);
    }

    public function testApplyCouponToCart()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'cart_details' => [
                'coupons' => self::COUPONS,
                'checkout_session_id' => $this->checkoutSessionId
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString(self::COUPONS[0]['coupon_code'], json_encode($response));
    }

    public function testUpdateCouponCode()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $newCode = 'CouponCode5';
        $requestData = [
            'cart_details' => [
                'coupons' => [
                    [
                        'coupon_code' => $newCode
                    ]
                ],
                'checkout_session_id' => $this->checkoutSessionId
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString($newCode, json_encode($response));
    }

    public function testRemoveCouponCode()
    {
        $this->createCart();
        $this->addItemToCart();

        $serviceInfo = self::SERVICE_INFO;
        $serviceInfo['rest']['resourcePath'] = str_replace('{cartId}', $this->createdCartId, self::PATH);

        $requestData = [
            'cart_details' => [
                'coupons' => [
                    [
                        'coupon_code' => ''
                    ]
                ],
                'checkout_session_id' => $this->checkoutSessionId
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertStringContainsString('"coupon_code":""', json_encode($response));
    }
}
