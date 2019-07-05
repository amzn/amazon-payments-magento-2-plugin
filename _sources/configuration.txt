Configuration
=============

After the successful installation you can proceed to the configuration. In Magento 2 admin go to `Stores --> Configuration --> Sales --> Payment Methods`. Click the **Configure** button next to the Amazon Pay logo.

.. image:: /images/configuration_amazon_pay.png

You now have to choose whether you already have an existing Amazon Pay merchant account or if you want to register a new account. Please be aware that the base currency of the shop is used to decide in which region (US, UK, EU, etc.) the account will be registered. If you do have questions on the registration process, please contact Amazon Pay merchant support at https://sellercentral-europe.amazon.com/cu/contact-us

.. image:: /images/configuration_amazon_pay_new-or-existing.png

After completing the account registration, or if you already have an existing account, you can continue with the plugin configuration as described in the following sections. 

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
Select the region where you registered your seller account from the provided list. If you're unsure about this information, please consult the Amazon Pay merchant support. Supported regions are:
* Euro (use for countries that use EUR as their currency, e.g. Germany, France, Italy, Spain, etc.)
* United Kingdom
* United States
* Japan

Sandbox
.......
Sandbox mode has been designed to test the **Amazon Pay** service. In sandbox mode the selected payment method is not charged. Refer to the **Amazon Pay** documentation to get more information about the sandbox environment. In general, sandbox mode should be enabled for development and staging environments for testing and always has to be disabled for production environments. Never show the sandbox buttons and widgets to buyers in your live environment.

Allowed Javascript Origins, Allowed Return URLs, IPN URL
........................................................
These are URLs that are required by Amazon Pay and should be added to your Seller Central account. These URLs are built using the Base URL which can be found under :menuselection:`General --> Web`.

* `Allowed Javascript Origins, Allowed Return URLs` - Please add this information to your Login with Amazon section of the Seller Central
* `IPN URL` - Please add this information to the field **Merchant URL** at :menuselection:`Settings --> Integration Settings` of the Seller Central

.. note:: The IPN URL settings for the Sandbox - and Production View differ. Please add the correct value to the environment you are currently transacting on.

Options
'''''''

Enable Amazon Pay
......................
By switching this option you can enable or disable **Amazon Pay**. This option must be enabled if you want to provide the Amazon Pay service to your customers.

Enable Login with Amazon
........................
By switching this option you can toggle **Login with Amazon**. When enabled this will log customers into Magento via their Amazon account. If disabled, customers using Amazon Pay will be handled as guests.

.. note:: Login with Amazon requires that Amazon Pay is enabled in order to function.

Payment Action
..............
* `Charge on shipment` (default) - Payments are authorized when an order is placed automatically. Captures must be requested manually by creating an invoice and selecting `Capture online`
* `Charge on order` - Payments are immediately authorized and captured.

Authorization Mode
..................
* `Immediate` (default) - The authorization is processed immediately during the checkout.
* `Automatic` - The authorization is processed during the checkout. In case this call times out, an asynchronous authorization will be done afterwards.

Independent of the mode you decide for, make sure to only orders which are successfully authorized by Amazon Pay (order state: `Processing`).

.. note:: If you expect high order values, the **Automatic** authorization mode might be the best choice for your business.

Update Mechanism
................
* `Data polling via Cron Job` (default) - Pull based mechanism where Magento 2 periodically checks authorization, capture  and refund status against the Amazon Pay systems. This is set to run at 5 minute intervals and requires that Magento 2 cron is setup and running
* `Instant Payment Notifications` - Push based mechanism where Amazon Pay pushes authorization, capture and refund status updates to Magento 2. This requires that your site has a valid SSL certificate

 
Advanced
''''''''

Frontend
........

Button Display Language
-----------------------
Allows input of a locale string to control button language should be in the format `en-gb`. By default the language of the store view is used.

Button Color
------------
Allows selection of button color from a pre determined list.

Show Amazon Pay button on product page
--------------------------------------
Toggles whether to show the **Amazon Pay** button on the product detail pages.

Show Amazon Pay button in minicart
----------------------------------
Toggles whether to show the **Amazon Pay** button in the Magento minicart.

Show Login with Amazon in authentication popup
----------------------------------------------
Toggles whether to show **Login with Amazon** button in the Magento authentication popup.

Show Amazon Pay Method
----------------------
If enabled, Amazon Pay is presented as an option in the list of available payment methods during the final step of checkout.

Sales Options
.............

Use Multi-currency
------------------
	
Enables the multi-currency feature of Amazon Pay for Magento 2.

.. note:: Multi-currency is currently supported for payment region EU and UK only, and only on Magento 2.3.1 and higher. If you are using a different payment region or Magento 2 version, this option will not be available.

The feature includes the following currencies:

* Australian Dollar (AUD)
* British Pound (GBP)
* Danish Krone (DKK)
* Euro (EUR)
* Hong Kong Dollar (HKD)
* Japanese Yen (JPY)
* New Zealand Dollar (NZD)
* Norwegian Krone (NOK)
* South African Rand (ZAR)
* Swedish Krone (SEK)
* Swiss Franc (CHF)
* United States Dollar (USD)

The Amazon Pay multi-currency feature is designed for international merchants who list prices in more than one currency on their website and charge their customers the exact amount quoted on the site. When you enable multi-currency, you are not limited by the currency associated with your Amazon Payments merchant account (the ledger currency in which you receive disbursements from Amazon Payments). The multi-currency feature is offered by Amazon Services Europe SARL.

The benefit to your customers is that they don’t need to worry about currency conversion or rates when shopping with their Amazon account. Any of our global 300MM Amazon buyers can check-out on your website with their existing Amazon account.

Store Name
----------
Allows setting the store name submitted to Amazon Pay per Store View. 

.. note:: Store View name is provided by default.

Developer Options
.................

Logging
-------
Enabled by default. This toggles whether to log all API calls and IPN notifications or not. The log files can be retrieved directly via the Magento 2 admin at :menuselection:`System --> Amazon Pay Logs --> Client`, respectively :menuselection:`System --> Amazon Pay Logs --> IPN`

Allowed IPs
-----------
For testing or debugging purposes you can restrict access to **Amazon Pay** checkout in your shop to certain IP addresses only. **Amazon Pay** button will be shown only for the visitors coming from allowed IPs. You can set more than one allowed IP, separated with commas.

.. note:: Due to caching restrictions this setting is not reflected on Product pages, Please  disable `Amazon Pay button is visible on Product Page` in this instance

Developer Logs
--------------
Downloads a copy of the developer logs of the extension.