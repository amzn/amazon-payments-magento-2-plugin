/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/list': {
                'Amazon_Pay/js/view/payment/list-mixin': true
            },
            'Magento_Tax/js/view/checkout/summary/grand-total': {
                'Amazon_Pay/js/view/checkout/summary/grand-total-mixin': true,
                'Amazon_Payment/js/view/checkout/summary/grand-total-mixin': false
            },
            'Magento_Checkout/js/view/form/element/email': {
                'Amazon_Pay/js/view/form/element/email': true
            },
            'Magento_Checkout/js/view/shipping-address/list': {
                'Amazon_Pay/js/view/shipping-address/list': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Amazon_Pay/js/view/shipping-address/address-renderer/default': true
            },
            'Magento_PurchaseOrder/js/view/checkout/shipping-address/address-renderer/default': {
                'Amazon_Pay/js/view/shipping-address/address-renderer/default': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Amazon_Pay/js/view/billing-address': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Amazon_Pay/js/view/shipping': true
            }
        }
    },
    map: {
        '*': {
            amazonPayProductAdd: 'Amazon_Pay/js/amazon-product-add',
            amazonPayButton: 'Amazon_Pay/js/amazon-button',
            amazonPayConfig: 'Amazon_Pay/js/model/amazonPayConfig',
            amazonPayLoginButton: 'Amazon_Pay/js/amazon-login-button',
            amazonPayLogout: 'Amazon_Pay/js/amazon-logout',
            amazonPayLogoutButton: 'Amazon_Pay/js/amazon-logout-button'
        }
    },
    paths: {
        amazonPayCheckoutDE: 'https://static-eu.payments-amazon.com/checkout',
        amazonPayCheckoutUK: 'https://static-eu.payments-amazon.com/checkout',
        amazonPayCheckoutJP: 'https://static-fe.payments-amazon.com/checkout',
        amazonPayCheckoutUS: 'https://static-na.payments-amazon.com/checkout'
    }
};
