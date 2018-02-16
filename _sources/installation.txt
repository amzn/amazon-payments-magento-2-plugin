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

* Sign in to your server via SSH
* `cd` into you Magento installation directory
* Install the extension via composer: `composer require amzn/amazon-payments-magento-2-plugin:^1.2.5`
* Enable the extension: `php bin/magento module:enable Amazon_Core Amazon_Login Amazon_Payment`
* Upgrade the Magento installation: `php bin/magento setup:upgrade`
* Follow any advice the upgrade routine provides

.. note:: `composer require amzn/amazon-payments-magento-2-plugin:^1.2.5` will always install the most current, non-breaking, Amazon Pay extension for you, when you run an update. To fix it to a specifix version, please remove the `^`

In production mode, you will also have to compile the code and the dependency injection (DI) configuration and deploy static content

* Compile code and DI: `php bin/magento setup:di:compile`
* Deploy static view files: `php bin/magento setup:static-content:deploy xx_XX yy_YY` where xx_XX, yy_YY, ... are the locales you are aiming to support
* Check permissions on directories and files and set them correctly if needed

.. note:: 
   Please also have a look at the official Magento documentation for command line configuration: http://devdocs.magento.com/guides/v2.1/config-guide/cli/config-cli-subcommands.html

Un-install Method
--------------------------
If there is a need to disable the module, you can either disable Amazon Pay and Login with Amazon in the extension settings. This will remove all customer facing parts.

To completely disable the module, please run `php bin/magento module:disable Amazon_Core Amazon_Login Amazon_Payment`

To completely uninstall the module using composer, please run `composer remove amzn/amazon-payments-magento-2-plugin`
