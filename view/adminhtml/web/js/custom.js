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
    // custom hide and show of button language field dependent on the payment region
    let regionField = $('[data-ui-id="select-groups-amazon-pay-groups-credentials-fields-payment-region-value"]');
    regionField.change(
        function() {
            let regions = ['de', 'uk'],
                languageRow = $('#row_payment_us_amazon_pay_advanced_frontend_display_language'),
                languageId = $('[data-ui-id=text-groups-amazon-pay-groups-advanced-groups-frontend-fields-display-language-value]'),
                value = $(this).val();
            if (regions.includes(value)) {
                languageRow.show();
            }
            else {
                languageId.val('');
                languageRow.hide();
            }
        });
    regionField.change();
});
