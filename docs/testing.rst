Testing your integration
========================

After a successful configuration, you should test your installation. Only after successfully testing in the Sandbox mode you should switch to the live environment and make the button visible for all your customers.

These tests should cover the different workflows that you encounter while processing orders. Include the standard process like receiving an order, invoicing, shipment and alternative processes like refunding orders. Verify that all objects in your Magento admin are in the expected status and you correctly received all order information including the shipping address, contact details and the billing address (if applicable).

Next you should test also declines. You can use the sandbox Toolbox to simulate soft and hard declines of authorizations. After your testing verify the log files to make sure no exceptions have occurred.

To receive the complete testing scenarios contact Amazon Pay.

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

Available Simulations
---------------------

.. image:: /images/testing_simulations.png

No Simulation
'''''''''''''

No simulation will be done. All payment flows will be successful.

Authorization soft decline
''''''''''''''''''''''''''

The payment authorization will come back as declined. For this kind of decline, customers are able to react, leading to a payment that can be taken.
Depending on your :doc:`configuration` the customer can react in the checkout or will be notified via email with instructions. 

Authorization hard decline
''''''''''''''''''''''''''

The payment authorization will come back as declined. For this kind of decline, customers are not able to react, leading to a payment that can not be taken.
Depending on your :doc:`configuration` the customer will be informed in the checkout or via email. 

Authorization timed out
'''''''''''''''''''''''

The payment authorization will come back as declined. For this kind of decline, customers are not able to react, leading to a payment that can not be taken.
This is a form of decline which will only happen for synchronous authorizations.

.. note:: When using `Synchronous if possible` in your :doc:`configuration`, this decline will lead to an asynchronous authorization.

Capture declined
''''''''''''''''

The capture will come back as declined. You will receive a notification in the Magento admin, informing you about this.

Capture pending
'''''''''''''''

The Capture will come back pending. The invoice will be pending as well. Depending on oyur update strategy, the IPN or polling mechanism will update the invoice accordingly.

Refund declined
'''''''''''''''

The refund will come back as declined. There will be a notification in the Magento admin, informing you about this.