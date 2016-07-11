Testing your integration
========================

After a successful configuration, you should test your installation. Only after successfully testing in the Sandbox mode you should switch to the live environment and make the button visible for all your sellers.

These tests should cover the different workflows that you encounter while processing orders. Include the standard process like receiving an order, invoicing, shipment and alternative processes like refunding orders. Verify that all objects in your Magento admin are in the expected status and you correctly received all order information including the shipping address, contact details and the billing address (if applicable).

Next you should test also declines. You can use the Sandbox Toolbox to simulate soft and hard declines of authorizations. After your testing verify the log files to make sure no exceptions have occurred.

To receive the complete testing scenarios contact Amazon Payments.

How to create Sandbox test account
----------------------------------

To use the sandbox environment, you need to create specific test accounts for the sandbox environment.

* Login into `Seller Central <https://sellercentral-europe.amazon.com>`_.
* Choose the menu :menuselection:`Integration --> Test accounts`.

.. image:: /images/seller-central/testing_screenshot_1.png

* Click on :guilabel:`Create a new test account`.

.. image:: /images/seller-central/testing_screenshot_2.png

* Fill in the form using a valid email address. The account can be used immediately after the account creation.

.. image:: /images/seller-central/testing_screenshot_3.png

* Add other delivery addresses to the test account (optional).

.. image:: /images/seller-central/testing_screenshot_4.png
