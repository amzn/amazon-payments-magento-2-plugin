Prerequisites
=============

System requirements
-------------------

**Login and Pay with Amazon** Magento extension requires you to have a valid **Login and Pay with Amazon** account (refer to the :ref:`prerequisites-amazon-account-setup` if you don't have one yet) and a webserver running a Magento 2 store instance with following conditions met:

* Magento CE (2.1.0)
* cURL for PHP
* DOM / XML for PHP
* valid SSL certificate (for **Login with Amazon** and / or IPN handling)

.. _prerequisites-amazon-account-setup:

**Login and Pay with Amazon** account setup
-------------------------------------------


Registering an Amazon Payments Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

* Go to:

  * DE merchants: `https://payments.amazon.de/preregistration/lpa <https://payments.amazon.de/preregistration/lpa?ld=SPEXDEAPAMagento>`_
  * UK merchants: `https://payments.amazon.co.uk/preregistration/lpa <https://payments.amazon.co.uk/preregistration/lpa?ld=SPEXUKAPAMagento>`_
  * US merchants: `https://payments.amazon.com/signup <https://payments.amazon.com/signup?ld=SPEXUKAPAMagento2>`_
* Click :menuselection:`Sign Up --> I'm a merchant`

.. image:: /images/seller-central/prerequisites_screenshot_1.png

* Go through the questionnaire to find out if you qualify for using Amazon Payments, then click `Sign up now`
* At the moment you cannot add your **Login and Pay with Amazon** account to an existing Amazon merchant account. You have to register a new account specifically for Amazon Payments.
* Start registering a new account:

  * If you see the link `Would you like to create a new account using a different e-mail address? Click here`, please do so.
  * Enter a name for your business. In case this name is already taken, please choose a different one.
  * Enter an email address and a password. You should choose a role email address that will be read directly by the people responsible for the Amazon Payments integration. You should avoid general addresses like **info@** that are only forwarded to the general administration.
  * Choose a secure password.

.. image:: /images/seller-central/prerequisites_screenshot_2.png

* Please fill in all requested information about your merchant account.
* Please be careful to provide exact and correct data. All information you provide will be verified by Amazon Payments, and incorrect information will delay the verification process.

.. image:: /images/seller-central/prerequisites_screenshot_3.png

* Please provide charge information to finish the registration

.. image:: /images/seller-central/prerequisites_screenshot_4.png

* After your account is registered you will be forwarded to your Seller Central account.
* Please be aware that you cannot fully use your account yet. First you have to provide your identity data, and then the account has to go through the verification process.
* It may take some time until your sandbox is ready to use.


Entering identity data in Seller Central
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To get the verification process started, please log in to Seller Central.

For a combined account (**Login and Pay with Amazon** added to an existing account), please make sure that you have selected the `Amazon Payments – Production View` in the drop down menu on the top.

.. image:: /images/seller-central/prerequisites_screenshot_5.png

At :menuselection:`Settings --> Account Info` please provide the requested missing information. Especially it is crucial to provide the ID information for all relevant persons.

.. image:: /images/seller-central/prerequisites_screenshot_6.png


Verification Process / Verification of all given information by Amazon Payments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

After successful registration of the Amazon Payments seller account and entering the ID information Amazon Payments will check all information provided. Depending on the information provided Amazon Payments may request more information.

.. _prerequisites-registering-application-for-login-with-amazon:

Registering application for Login with Amazon service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Login and Pay with Amazon work together to provide a great buyer experience. To use **Login with Amazon** you have to register the application (Magento extension) that will be allowed to access buyers accounts through your Amazon Payments seller account. **Login with Amazon** configuration settings can be accessed through `Login with Amazon` Seller Central page.

.. image:: /images/seller-central/prerequisites_screenshot_7.png

In the App Console register a new application by clicking the `Register new Application` button. The `Register Your Application` form will appear.

.. image:: /images/seller-central/prerequisites_screenshot_8.png

In the application details page, add basic details about your web site. These details will be used on your website and mobile apps (if applicable).

* `Name Shown to Users`. This is the name displayed on the consent screen when the users agree to share the information with your web site. This name applies to website versions of your application.
* `Description`. A description of your web site for Login with Amazon users.
* `Privacy Notice URL`. The Privacy URL is the location of your company privacy policy. It is also displayed on the consent screen. This link is displayed to users when they first login to your application (for example: `http://www.example.com/privacy.html`).
* `Logo Image File`. This logo will represent your business or website on Amazon. The logo will be displayed as a 150x150 pixel image; if you upload a file of a different size, it will be scaled to fit.

When you are finished, click `Save` to save your changes.


Add a Website to your Application
'''''''''''''''''''''''''''''''''

* From the Application screen, click `Web Settings`. You will automatically be assigned values for Client ID, which identifies your website.

.. image:: /images/seller-central/prerequisites_screenshot_9.png

* To add Allowed JavaScript Origins to your application, click `Edit`.

An origin is the combination of protocol, your Magento shop domain name and port (for example: `https://www.example.com:8443`). Allowed origins must use the HTTPS protocol. If you are using a default port (443) you need only include the domain name of your shop (for example: `https://www.example.com`).

Adding your domain here allows the SDK for JavaScript to communicate with your Magento shop directly during the login process. Web browsers normally block cross-origin communication between scripts unless the script specifically allows it.

.. image:: /images/seller-central/prerequisites_screenshot_10.png

To add more than one origin (in case you are running domain based multi-store Magento installation and all stores are using the same Amazon Payments seller account), click `Add Another`.

.. note:: To use Login with Amazon with your Magento 2 shop, you **MUST** specify at least one allowed JavaScript origin.

.. note:: Please add all allowed JavaScript Origins and Allowed Redirect URLs your Amazon Payments configuration section of your Magento 2 shop asks you to add.

.. _prerequisites-where-to-find-the-required-credentials:

Where to find the required credentials to configure the Magento 2 extension
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All credentials are listed in your Seller Central account :menuselection:`Integration --> MWS Access Key`.

.. image:: /images/seller-central/prerequisites_screenshot_11.png

Magento 2 extension requires you to enter information about your Amazon Payments account. This can be copied as a json string and pasted into Magento.

You can find this information in your Amazon Payments seller account at :menuselection:`Integration --> MWS Access Key`.

.. image:: /images/copy-your-keys.png

Configuration required in Seller Central
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're planning to use IPN for the post-payment processing you need to enter a Merchant URL (IPN endpoint URL) in Seller Central. 

You can do this at :menuselection:`Settings --> Integration Settings`, then click the `Edit` button at `Instant Notification Settings`.

Your IPN endpoint URL can be obtained from Magento admin at :menuselection:`Stores --> Configuration --> Sales --> Payment Methods --> Amazon Payments --> General --> Credentials --> IPN URL`.
