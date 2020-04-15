# Amazon Pay Checkout v2

This module will enable "Amazon Pay Checkout v2" on your Magento 2 installation. Amazon Pay Checkout v2 is the next generation web checkout technology of Amazon Pay that provides several advantages over the previous Amazon Pay Checkout solution.

## What's new in Amazon Pay Checkout v2?

* Hosted checkout experience (replacing widgets solution of the previous module)
* Fewer checkout steps (merging consent and address/payment selection screen)
* Avoids problems on browsers that have active cookie blocking or tracking protection mechanisms
* Supports virtual goods
* Automatic, graceful handling of declined authorization, increasing checkout conversion rate
* Integrated support for PSD2/SCA (Strong Customer Authentication)

## About Amazon Pay

Amazon Pay offers a familiar and convenient buying experience that can help your customers spend more time shopping and less time checking out. Amazon Pay is used by large and small companies. From years of shopping safely with Amazon, customers trust their personal information will remain secure and know many transactions are covered by the Amazon A-to-z Guarantee. Businesses have the reassurance of our advanced fraud protection and payment protection policy.

## Dependencies

You can find a list of modules in the require section of the `composer.json` file located in the
same directory as this `README.md` file.

## Installation Steps

**Important:** Before proceeding, please make a backup of your current Magento 2 installation.

### 1. Install module

The module can be either installed via Composer (recommended), or manually. The steps for each option are described below. 

#### Composer installation

In `magento-root`, execute:

```
$ composer require amzn/amazon-payments-magento-2-plugin:dev-V2checkout
$ touch app/etc/.amazon_payv2_enable
$ bin/magento module:enable Amazon_PayV2
```

If Composer installation didn't work, use the manual procedure below. If any of these were successful, please proceed with **2. Post-installation procedure**, otherwise reach out to Amazon Pay Merchant Support for additional assistance.

#### Manual installation
* Download the [Amazon Pay V2 checkout plugin](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout) via `git clone` or "Download ZIP"
* Copy src/PayV2 to app/code/Amazon/PayV2  
(If `magento-root/app/code/Amazon/PayV2` path is not present, please create the folders `Amazon` and `PayV2`)  

In `magento-root`, execute:
```
$ composer require amzn/amazon-pay-sdk-v2-php
$ composer require aws/aws-php-sns-message-validator
$ touch app/etc/.amazon_payv2_enable
$ bin/magento module:enable Amazon_PayV2
```

### 2. Post-installation procedure

Execute the following steps to perform the module upgrade, compile dependency injection, deploy static content and clean caches.

```
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
$ bin/magento setup:static-content:deploy
$ bin/magento cache:clean
```

## PWA Support

1. The module exposes the REST endpoints that needs to set up. You can find them at [src/PayV2/etc/webapi.xml](https://github.com/amzn/amazon-payments-magento-2-plugin/blob/V2checkout/src/PayV2/etc/webapi.xml)
1. The front end needs to be setup by the merchant/developer.

## Extension Points

Amazon Pay does not provide any specific extension points.

## Configuration

### Amazon Pay V2 configuration ###

Upon successful installation of the module, please follow the steps below for configuring it:

1. Go to Stores -> Configuration -> Sales -> Payment Methods -> Amazon Pay -> Configure
1. Switch to 'V2' under the Amazon Pay Product Version
1. Under 'Private Key' field, click on the 'Generate a new public/private key pair for Amazon Pay'. This saves the Private Key in the settings and displays the text [encrypted]
1. Click 'Download Public Key' to save the Public Key locally
1. To obtain the Public Key ID, please log in to your Amazon Pay merchant account via Seller Central.
1. In the dropdown box on top, select "Amazon Pay (Production View").
1. In the menu, select "Integration" > "Integration Central"
1. Under "Technical guidance and API credentials", apply the selection shown below.
  ![](https://github.com/amzn/amazon-payments-magento-2-plugin/blob/master/docs/images/seller-central-wizard-selection.png?raw=true)
1. In the "API access" section that will show up further down the page, copy the "Merchant ID" and "Store ID" and copy them into the corresponding fields of the plugin configuration.
  ![](https://github.com/amzn/amazon-payments-magento-2-plugin/blob/master/docs/images/seller-central-merchantId-storeId.png?raw=true)
1. Back in Seller Central, click "Create Keys".
1. As shown below, chose to register an "existing public key" and copy/paste the content of the public key that the plugin has generated into the box. Then click "Create Keys".
  ![](https://github.com/amzn/amazon-payments-magento-2-plugin/blob/master/docs/images/seller-central-key-registration.png?raw=true)
1. Back on the previous screen, click "Copy" below the newly created entry to receive your Public Key ID.
  ![](https://github.com/amzn/amazon-payments-magento-2-plugin/blob/master/docs/images/seller-central-copy-key.png?raw=true)
1. Paste the Public Key ID into the corresponding field on the configurations screen of the plugin.
1. Rest of the settings are all similar to the V1 settings. We recommend to use the same settings as used in V1. [View V1 Configuration documentation](https://amzn.github.io/amazon-payments-magento-2-plugin/configuration.html)
