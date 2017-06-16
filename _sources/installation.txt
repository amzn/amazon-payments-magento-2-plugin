Installation
============

Pre-installation steps
----------------------
* Create a backup of your shop before proceeding to install.

Web Setup Wizard / Magento Marketplace Install Method (Preferred)
-----------------------------------------------------------------
The installation via the Web Setup Wizard is the preferred method of installation.
Please follow the `Magento Marketplace User Guide`_ to learn how this works.

Our extension can be found here: https://marketplace.magento.com/amzn-amazon-pay-and-login-magento-2-module.html

.. _`Magento Marketplace User Guide`: http://docs.magento.com/marketplace/user_guide/quick-tour/install-extension.html 

Manual Composer Install Method
------------------------------
In case you are not able or willing to use the web installation, you can install the extension using composer.

- Sign in to your server via SSH
- `cd` into you Magento installation directory
- Install the extension via composer: `composer require amzn/amazon-payments-magento-2-plugin:^1.1.0`
- Enable the extension: `php bin/magento module:enable Amazon_Core Amazon_Login Amazon_Payment`
- Upgrade the Magento instalation: `php bin/magento setup:upgrade`
- Follow any advice the upgrade routine provides
- Deploy static content: `php bin/magento setup:static-content:deploy xx_XX yy_YY` where xx_XX, yy_YY, ... are the locales you are aiming to support
- Check permissions on directories and files and set them correctly if needed
