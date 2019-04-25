Migration: Magento 1 to Magento 2
=====================================
To migrate your Amazon Pay integration successfully from Magento 1 to Magento 2, please follow the instructions below carefully.

Step 1: Verify extension installation
-------------------------------------
With Magento 2.2.4 and higher, the Amazon Pay extension is bundled along with Magento 2. To verify if it's installed successfully, open the Magento 2 admin and go to Stores –> Configuration –> Sales –> Payment Methods. Amazon Pay should be listed under "OTHER PAYMENT METHODS" like shown below.

.. image:: /images/configuration_amazon_pay.png

If Amazon Pay is not listed here, please refer to the :doc:`installation` section to get more details concerning installation procedure.

Step 2: Configure the extension
-------------------------------
* Add the **Allowed JavaScript Origins and Allowed Return URLs** to your Login with Amazon section of the Seller Central. Please note that the Allowed JavaScript Origins URL must only be changed if the base URL of your shop has changed (e.g. www.myshop.com => www.mygreatnewshop.com ). The Allowed Return URL **must be** changed as this requires a full URL and is different from the one used in Magento 1.
* Add the **IPN URL** to the field Merchant URL at Settings ‣ Integration Settings of the Seller Central. Please note that the IPN URL can be specified separately for Production and Sandbox view, so please verify that it has been set correctly for both environment.
* Refer to :doc:`configuration` to get more information about the other configuration settings.

Step 3: Review the below settings
---------------------------------
In order to use the same settings you were using in your Magento 1 integration, please review the below table and choose the appropriate Amazon Pay settings for your shop. Please note that there are two different Amazon Pay extensions available for Magento 1, one for EU and one for US region. The table below lists the options from each extension and the corresponding option for Magento 2.

+--------------------+-------------------------+-----------------------+--------------------+
| Option             | Magento 1 (EU)          | Magento 1 (US)        | Magento 2          |
+====================+=========================+=======================+====================+
| Payment Action     | Authorize and capture   | Authorize and capture | Charge on order    |
|                    +-------------------------+-----------------------+--------------------+
|                    | Authorize               | Authorize             | Charge on shipment |
+--------------------+-------------------------+-----------------------+--------------------+
| Authorization Mode | Auto                    |                       | Automatic          |
|                    +-------------------------+-----------------------+--------------------+
|                    | Synchronous             | Async Mode: 'No'      | Immediate          |
+                    +-------------------------+-----------------------+--------------------+
|                    | Asynchronous            | Async Mode: 'Yes'     | Automatic          |
+--------------------+-------------------------+-----------------------+--------------------+
| Update Mechanism   | Instant Payment Notif.  | N/A                   | Instant Payment N. |
|                    +-------------------------+-----------------------+--------------------+
|                    | Data polling            | Default               | Data polling       |
+--------------------+-------------------------+-----------------------+--------------------+

Step 4: Front-end Customization
---------------------------------
If your are using a custom template please read the following sections from our documentation carefully: 

* :doc:`customisation`
* :doc:`faq`

Step 5: Test your integration
---------------------------------
Before go-live, please thoroughly test the integration as described in :doc:`testing`.

Step 6: Go live
---------------------------------
Now that your Magento 2 integration is up-to-date with your Magento 1 integration and is functioning normally, please diable the sandbox mode and remove any IP address whitelisting. It is best practice to perform a test transaction in live mode to ensure the integration is working as expected.