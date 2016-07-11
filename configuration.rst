Configuration
=============
After the successful installation you can proceed to the configuration. In Magento 2 admin go to :menuselection:`Stores --> Configuration --> Sales --> Payment Methods --> Amazon Payments`

General
-------

Credentials
'''''''''''

Merchant Id, Access Key Id, Secret Access Key, Client Id, Client Secret
.......................................................................
Amazon Payments seller credentials. They can be found in Amazon Seller Central.

Credentials JSON
................
JSON string of all Amazon Payments seller credentials, You can retrieve them via the copy your keys button in Amazon Seller Central.

.. note:: The values supplied in Credentials JSON will actually be used to set values for Merchant Id, Access Key Id, Secret Access Key, Client Id and Client Secret this value will be cleared on save.

Payment Region
..............
Select the region where you registered your seller account from the provided list. If you're unsure about this information consult your Amazon Integration Assistant.

Sandbox
.......
Sandbox mode has been designed to test the **Pay with Amazon** service. In sandbox mode the selected payment method is not charged. Refer to the **Pay with Amazon** documentation to get more information about the sandbox environment. In general, sandbox mode should be enabled for development and staging environments for testing and always has to be disabled for production environments. Never show the sandbox buttons and widgets to buyers in your live environment.

Javascript Origin, Redirect URL, IPN URL
........................................
Used to display the URLs within Magento 2 that are required by Amazon Payments these can be added to Amazon Seller Central.

Options
'''''''

Enable Pay With Amazon
......................
By switching this option you can enable or disable **Pay with Amazon**. This option must be enabled if you want to provide the Pay with Amazon service to your customers.

Enable Login with Amazon
........................
By switching this option you can toggle **Login with Amazon**. When enabled this will log customers into Magento via their Amazon account.

.. note:: Login with Amazon requires that Pay with Amazon be enabled in order to function.

Payment Action
..............
* `Charge on shipment` (default) - order reference creation is followed by automatic authorization request. Capture must be requested manually by creating an invoice and selecting `Capture online`
* `Charge on order` - order reference creation is followed by automatic authorization and capture request. It is mandatory that you get white-listed for this feature by Amazon Payments first. Do not activate this option without contacting Amazon Payments first.

Authorization Mode
..................
* `Synchronous` (default) - Authorization is processed immediately customer will get instant feedback if it is declined
* `Asynchronous` - Authorization processing is delayed customer will complete checkout straight away and authorization will be updated via IPN or Cron, customer will get email in case of decline
* `Synchronous if possible` - the same as Synchronous but if an Authorization fails due to timeout an Asynchronous authorization is placed allowing further processing time

Update Mechanism
................
* `Data polling via Cron Job` (default) - Pull based mechanism where Magento 2 periodically checks authorization/capture/refund status with Amazon. This is set to run at 5 minute intervals and requires that Magento 2 cron is setup and running
* `Instant Payment Notifications` - Push based mechanism where Amazon pushes authorization/capture/refund status updates to Magento. This requires that your site has a valid SSL certificate

Pay with Amazon button is visible on Product Page
.................................................
toggles whether to show **Pay with Amazon** on product pages
 
Advanced
--------

Frontend
''''''''

Button Display Language
.......................
Allows input of a locale string to control button language should be in the format `en-gb`.

Button Color
............
Allows selection of button color from a pre determined list.

Button Size
...........
Allows selection of button size from a pre determined list.

Sales Options
'''''''''''''

New Order Status
................
Allows selection of a custom status for orders with a `Processing` state made using the Amazon payment method.

Sales Exclusions
''''''''''''''''

Is Packing Stations Terms Validation Enabled
............................................
Toggles validation enabled for packing station terms, terms will be shown below when enabled.

Packing Stations Terms
......................
Comma seperated list of terms that will prevent shipping address selection if they are found in address lines.

Excluded Categories
...................
Allows selection of multiple categories, if a product has one of the assigned categories the **Pay with Amazon** buttons will not be shown on the product page and if it's in the basket you will not be able to pay with amazon at checkout and will instead be reverted to the standard Magento 2 checkout.

Developer Options
'''''''''''''''''

Logging
.......
Enabled by default this toggles whether to log all API calls and IPN notifications or not

Allowed IPs
...........
For testing or debugging purposes you can restrict access to **Pay with Amazon** checkout in your shop to certain IP addresses only. **Pay with Amazon** button will be shown only for the visitors coming from allowed IPs. You can set more than one allowed IP separated with commas.

.. note:: Due to caching restrictions this setting is not reflected on Product pages, Please  disable `Pay with Amazon button is visible on Product Page` in this instance