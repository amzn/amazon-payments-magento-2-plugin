Configuration
=============
After the successful installation you can proceed to the configuration. In Magento 2 admin go to :menuselection:`Stores --> Configuration --> Sales --> Payment Methods --> Amazon Pay`

General
-------

Credentials
'''''''''''

Credentials JSON
................
JSON string of all Amazon Pay seller credentials, You can retrieve them via the copy your keys button in Amazon Seller Central at :menuselection:`Integration --> MWS Access Key`.

.. note:: The values supplied in Credentials JSON will actually be used to set values for Merchant Id, Access Key Id, Secret Access Key, Client Id and Client Secret this value will be cleared on save.


Merchant Id, Access Key Id, Secret Access Key, Client Id, Client Secret
.......................................................................
Using the credentials JSON is the preferred and easiest way to supply your credentials. Manual configuration is possible as well.

The credentials can be found in Seller Central at :menuselection:`Integration --> MWS Access Key`.

.. image:: /images/seller-central/configuration_screenshot_1.png

Payment Region
..............
Select the region where you registered your seller account from the provided list. If you're unsure about this information, please consult the Amazon Pay merchant support.

Sandbox
.......
Sandbox mode has been designed to test the **Amazon Pay** service. In sandbox mode the selected payment method is not charged. Refer to the **Amazon Pay** documentation to get more information about the sandbox environment. In general, sandbox mode should be enabled for development and staging environments for testing and always has to be disabled for production environments. Never show the sandbox buttons and widgets to buyers in your live environment.

Javascript Origin, Redirect URL, IPN URL
........................................
Used to display the URLs within Magento 2 that are required by Amazon Pay. Please add this information to your Seller Central account.

* `Javascript Origin, Redirect URL` - Please add this information to your Login with Amazon section of the Seller Central
* `IPN URL` - Please add this information to the field **Merchant URL** at :menuselection:`Settings --> Integration Settings` of the Seller Central

.. note:: The IPN URL settings for the Sandbox - and Production View differ. Please add the correct value to the environment you are currently transacting on.

Options
'''''''

Enable Amazon Pay
......................
By switching this option you can enable or disable **Amazon Pay**. This option must be enabled if you want to provide the Amazon Pay service to your customers.

Enable Login with Amazon
........................
By switching this option you can toggle **Login with Amazon**. When enabled this will log customers into Magento via their Amazon account.

.. note:: Login with Amazon requires that Amazon Pay is enabled in order to function.

Payment Action
..............
* `Charge on shipment` (default) - Payments are authorized when an order is placed automatically. Captures must be requested manually by creating an invoice and selecting `Capture online`
* `Charge on order` - Payments are immediately authorized and captured.

Authorization Mode
..................
* `Synchronous` (default) - The authorization is processed during the checkout. 
* `Asynchronous` - The authorization is processed after the checkout was completed.
* `Synchronous if possible` - The authorization is processed during the checkout. In case this call times out, an asynchronous authorization will be done afterwards. 

Independent of the mode you decide for, make sure to only orders which are successfully authorized by Amazon Pay (order state: `Processing`).

.. note:: If you expect high order values, the **asynchronous** authorization might be the best chioce for your business.

Update Mechanism
................
* `Data polling via Cron Job` (default) - Pull based mechanism where Magento 2 periodically checks authorization, capture  and refund status against the Amazon Pay systems. This is set to run at 5 minute intervals and requires that Magento 2 cron is setup and running
* `Instant Payment Notifications` - Push based mechanism where Amazon Pay pushes authorization, capture and refund status updates to Magento 2. This requires that your site has a valid SSL certificate

Amazon Pay button is visible on Product Page
.................................................
toggles whether to show **Amazon Pay** on product pages
 
Advanced
--------

Frontend
''''''''

Button Display Language
.......................
Allows input of a locale string to control button language should be in the format `en-gb`. By default the language of the store view is used.

Button Color
............
Allows selection of button color from a pre determined list.

Sales Options
'''''''''''''

New Order Status
................
Allows selection of a custom status for orders with a `Processing` state made using the Amazon Pay payment method. 

.. note:: This status indicates, if a payment for the order was authorized by Amazon Pay

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
Allows selection of multiple categories, if a product has one of the assigned categories the **Amazon Pay** buttons will not be shown on the product page and if it's in the basket you will not be able to pay with Amazon Pay at checkout and will instead be reverted to the standard Magento 2 checkout.

Developer Options
'''''''''''''''''

Logging
.......
Enabled by default. This toggles whether to log all API calls and IPN notifications or not. The log files can be retrieved directly via the Magento 2 admin at :menuselection:`System --> Amazon Pay Logs --> Client`, respectively :menuselection:`System --> Amazon Pay Logs --> IPN`

Allowed IPs
...........
For testing or debugging purposes you can restrict access to **Amazon Pay** checkout in your shop to certain IP addresses only. **Amazon Pay** button will be shown only for the visitors coming from allowed IPs. You can set more than one allowed IP, separated with commas.

.. note:: Due to caching restrictions this setting is not reflected on Product pages, Please  disable `Amazon Pay button is visible on Product Page` in this instance