Login with Amazon
=================
**Login with Amazon** allows users to login to your shop using their Amazon username and password. The name, email address and user id is fetched from Amazon in order to facilitate the creation of a Magento account. Shipping and billing address will be retrieved as part of the checkout.

Customers who have logged in via Login with Amazon previously, will be recognized and automatically logged in into the Magento customer account as well. In case an account for the email address already exists, the accounts can be linked to add **Login with Amazon** as another option to sign in for the customer.

Requirements
------------
To offer the **Login with Amazon** service, you have to have a valid **Amazon Pay** merchant account (`sign up here`_ if you don't have one yet), and registered an "Login with Amazon" application (see `LWA Client ID guide`_ for the setup).

.. _`sign up here`: https://pay.amazon.com/signup
.. _`LWA Client ID guide`: https://images-na.ssl-images-amazon.com/images/G/02/amazonservices/payments/website/AmazonPay_ClientID_UK._TTD_.pdf

`Login with Amazon` button
--------------------------
The `Login with Amazon` button appears in several places in the shop:

* on the customer login page
* on the customer registration page

.. image:: /images/register.png

Pressing the `Login with Amazon` button launches the Amazon authentication window, where the customer is asked for their Amazon account e-mail address and password.

.. image:: /images/sign-in.png

After a successful login the customer is redirected to the `My Account` section.
