Login with Amazon
=================
**Login with Amazon** allows users to login to your shop using their Amazon username and password. The name, email address and user id is fetched from Amazon in order to facilitate the creation of a Magento account shipping and billing address will be retrieved as part of checkout.

Customers who have logged in with Amazon previously will have a record in your database to link their Magento account to their Amazon one, If a customer is already logged in and additionally logs in with Amazon the 2 account will be linked and if a Magento customer is found with the same email address as that used on Amazon they will be asked to confirm their Magento password which will then link the 2 accounts.

Requirements
------------
**Login with Amazon** service requires you to have a valid **Login and Pay with Amazon** account (refer to the :ref:`prerequisites-amazon-account-setup` if you don't have one yet), registered application for **Login with Amazon** service (refer to the :ref:`prerequisites-registering-application-for-login-with-amazon` if you don't have one yet) and Magento 2 store with a valid SSL certificate installed and properly configured in your shop.

`Login with Amazon` button
--------------------------
The `Login with Amazon` button appears in several places in the shop:

* on the customer login page
* on the customer registration page

.. image:: /images/register.png

Pressing the `Login with Amazon` button launches the Amazon authentication window, where the customer is asked for their Amazon account e-mail address and password.

.. image:: /images/sign-in.png

After a successful login the customer is redirected to the `My Account` section.
