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
require(['jquery', 'domReady!'], function ($) {
    $('[data-ui-id="text-groups-amazon-pay-groups-credentials-fields-public-key-id-value"]').blur(
        function() {
            $(this).val($(this).val().toUpperCase().trim());
        });

    $('[data-ui-id="text-groups-amazon-pay-groups-credentials-fields-merchant-id-v2-value"]').blur(
        function() {
            $(this).val($(this).val().toUpperCase().trim());
        });

    $('[data-ui-id="text-groups-amazon-pay-groups-credentials-fields-store-id-value"]').blur(
        function() {
            $(this).val($(this).val().toLowerCase().trim());
        });

    // private key select pem file
    $('#private_key_pem_button').click(function (e) {
        e.preventDefault();

        // set selected type
        $('#payment_us_amazon_pay_credentials_private_key_selected').val('pem');
        // uncheck inherit
        $('#payment_us_amazon_pay_credentials_private_key_selected_inherit').prop('checked', true).click();
        // hide text row
        $('#row_payment_us_amazon_pay_credentials_private_key_text').hide();
        $('#payment_us_amazon_pay_credentials_private_key_text').val('------');
        // hide selector row
        $('#row_payment_us_amazon_pay_credentials_private_key_selector').hide();
        // remove saved file feedback text
        $('#amazon_pay_private_key_pem_file_saved_msg').html('');
        // show pem row
        $('#row_payment_us_amazon_pay_credentials_private_key_pem').show();
        // click pem choose file button
        $('#payment_us_amazon_pay_credentials_private_key_pem').click();
    });

    // private key select text
    $('#private_key_text_button').click(function (e) {
        e.preventDefault();

        // set selected type
        $('#payment_us_amazon_pay_credentials_private_key_selected').val('text');
        // uncheck inherit
        $('#payment_us_amazon_pay_credentials_private_key_selected_inherit').prop('checked', true).click();
        // hide file row
        $('#row_payment_us_amazon_pay_credentials_private_key_pem').hide();
        // hide selector row
        $('#row_payment_us_amazon_pay_credentials_private_key_selector').hide();
        // show text area row
        $('#row_payment_us_amazon_pay_credentials_private_key_text').show();
        // focus on field
        $('#payment_us_amazon_pay_credentials_private_key_text').val('').focus();
    });

    // change key type
    $('.amazon-private-key-change-key-type').click(function (e) {
        e.preventDefault();

        // reset selected type
        $('#payment_us_amazon_pay_credentials_private_key_selected').val('');
        // check inherit
        $('#payment_us_amazon_pay_credentials_private_key_selected_inherit').prop('checked', false).click();
        // set text field
        $('#payment_us_amazon_pay_credentials_private_key_text').val('------');
        // hide pem row
        $('#row_payment_us_amazon_pay_credentials_private_key_pem').hide();
        // hide text row
        $('#row_payment_us_amazon_pay_credentials_private_key_text').hide();
        // show selector row
        $('#row_payment_us_amazon_pay_credentials_private_key_selector').show();
    });

    $('#payment_us_amazon_pay_credentials-head').click(function () {
        showPrivateKey($(this), $('#payment_us_amazon_pay_credentials_active_v2').val() == 1);
    });

    $('#payment_us_amazon_pay_credentials_active_v2').change(function () {
        showPrivateKey($(this), $(this).val() == 1);
    });

    function showPrivateKey(field, enabled) {
        if (enabled) {
            let value = $('#payment_us_amazon_pay_credentials_private_key_selected').val();
            if (value === 'pem') {
                $('#row_payment_us_amazon_pay_credentials_private_key_selector').hide();
                $('#row_payment_us_amazon_pay_credentials_private_key_pem').show();
            } else if (value === 'text') {
                $('#row_payment_us_amazon_pay_credentials_private_key_selector').hide();
                $('#row_payment_us_amazon_pay_credentials_private_key_text').show();
            } else {
                $('#row_payment_us_amazon_pay_credentials_private_key_selector').show();
                $('#payment_us_amazon_pay_credentials_private_key_text').val('------');
            }
        }
        else {
            $('#row_payment_us_amazon_pay_credentials_private_key_selector').hide();
            $('#row_payment_us_amazon_pay_credentials_private_key_pem').hide();
            $('#row_payment_us_amazon_pay_credentials_private_key_text').hide();
        }
    }
});
