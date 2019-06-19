Installation
============

The extension is available via composer, Magento Marketplace or, with Magento 2.2.4 and higher, already pre-installed as bundled extension.

.. note:: If you are using Magento 2.2.4 or higher, the extension is probably already pre-installed and you can head straight to :doc:`configuration`.

Pre-installation steps
----------------------
* Create a backup of your shop before proceeding to install.

Web Setup Wizard / Magento Marketplace Install Method
-----------------------------------------------------------------
The installation via the Web Setup Wizard is the recomennded method of installation for our extension when using Magento 2.1 - 2.2.3. For shops using Magento 2.2.4 or higher, the composer method outlined below is recommended.
Please follow the `Magento Marketplace User Guide`_ to learn how this works.

Our extension can be found here: https://marketplace.magento.com/amzn-amazon-pay-and-login-magento-2-module.html

.. _`Magento Marketplace User Guide`: http://docs.magento.com/marketplace/user_guide/quick-tour/install-extension.html

Manual Composer Install Method
------------------------------
When using Magento 2.2.4, the extension is probably already pre-installed. If not, please follow this procedure to install the extension.

.. note:: The composer require command below will always install the most current, non-breaking, Amazon Pay extension for you, when you run an update. To fix it to a specifix version, please replace the version behind the colon with the preferred version.

* Sign in to your server via SSH.
* `cd` into you Magento installation directory.
* Install the extension via composer. The right command is dependent on your Magento 2 version:

    * Magento 2.1 - 2.2.3: `composer require amzn/amazon-payments-magento-2-plugin:1.2.*`
    * Magento 2.2.4 - 2.2.5: `composer require amzn/amazon-payments-magento-2-plugin:2.0.*`
    * Magento 2.2.6 - 2.2.x: `composer require amzn/amazon-payments-magento-2-plugin:2.2.*`
    * Magento 2.3.0 and above: `composer require amzn/amazon-payments-magento-2-plugin:3.*`
* Enable the extension: `php bin/magento module:enable Amazon_Core Amazon_Login Amazon_Payment`
* Upgrade the Magento installation: `php bin/magento setup:upgrade`
* Follow any advice the upgrade routine provides
* Compile code and dependency injection: `php bin/magento setup:di:compile`
* Deploy static view files (production mode only): `php bin/magento setup:static-content:deploy xx_XX yy_YY` where xx_XX, yy_YY, ... are the locales you are aiming to support
* Check permissions on directories and files and set them correctly if needed

.. note::
   Please also have a look at the official Magento documentation for command line configuration: http://devdocs.magento.com/guides/v2.1/config-guide/cli/config-cli-subcommands.html

Un-install Method
--------------------------
If there is a need to disable the module, you can either disable Amazon Pay and Login with Amazon in the extension settings. This will remove all customer facing parts.

To completely disable the module, please run `php bin/magento module:disable Amazon_Core Amazon_Login Amazon_Payment`

To completely uninstall the module using composer, please run `composer remove amzn/amazon-payments-magento-2-plugin`
