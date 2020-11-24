# Amazon Pay Checkout v2

This module will enable "Amazon Pay Checkout v2" on your Magento 2 installation. Amazon Pay Checkout v2 is the next generation web checkout technology of Amazon Pay that provides several advantages over the previous Amazon Pay Checkout solution.

## What's new in Amazon Pay Checkout v2?

* Hosted checkout experience (replacing widgets solution of the previous module)
* Fewer checkout steps (merging consent and address/payment selection screen)
* Avoids problems on browsers that have active cookie blocking or tracking protection mechanisms
* Supports virtual goods
* Automatic, graceful handling of declined authorization, increasing checkout conversion rate
* Integrated support for PSD2/SCA (Strong Customer Authentication)
* Alexa Notifications feature support

## About Amazon Pay

Amazon Pay offers a familiar and convenient buying experience that can help your customers spend more time shopping and less time checking out. Amazon Pay is used by large and small companies. From years of shopping safely with Amazon, customers trust their personal information will remain secure and know many transactions are covered by the Amazon A-to-z Guarantee. Businesses have the reassurance of our advanced fraud protection and payment protection policy.

Requirements:
* Magento minimum version requirement: 2.4.0 and above
* Amazon Pay plugin minimum version requirement: 4.0.2 and above
* Supported PHP versions: 7.3 and 7.4

## Dependencies

You can find a list of modules in the require section of the `composer.json` file located in the
same directory as this `README.md` file.

## Installation and Configuration
If you are on M2.2.6 and above or M2.3.x, please use [V2checkout-1.2.x](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout-1.2.x) branch. The [README.md](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout-1.2.x#amazon-pay-checkout-v2) contains all the installation and configuration information.

## Installation Steps

**Important:** Before proceeding, please make a backup of your current Magento 2 installation.

### 1. Install module

The module can be either installed via Composer (recommended), or manually. The steps for each option are described below. 

#### Composer installation

In `magento-root`, execute:

```
$ composer require amzn/amazon-pay-v2-magento-2-module
$ bin/magento module:enable Amazon_PayV2 --clear-static-content
```

If Composer installation didn't work, use the manual procedure below. If any of these were successful, please proceed with **2. Post-installation procedure**, otherwise reach out to Amazon Pay Merchant Support for additional assistance.

#### Manual installation
* Download the [Amazon Pay V2 checkout plugin](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout) via `git clone` or "Download ZIP"
* Copy src/PayV2 to app/code/Amazon/PayV2  
(If `magento-root/app/code/Amazon/PayV2` path is not present, please create the folders `Amazon` and `PayV2`)  

In `magento-root`, execute:
```
$ composer require amzn/amazon-pay-api-sdk-php
$ composer require aws/aws-php-sns-message-validator
$ bin/magento module:enable Amazon_PayV2 --clear-static-content
```

### 2. Post-installation procedure

Execute the following steps to perform the module upgrade, compile dependency injection, deploy static content and clean caches.

```
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
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
1. To obtain the required keys, please log in to your Amazon Pay merchant account via Seller Central and follow [these instructions](http://amazonpaycheckoutintegrationguide.s3.amazonaws.com/amazon-pay-checkout/get-set-up-for-integration.html#4-get-your-public-key-id) to receive your Public Key Id. You will also need the associated secret key in order to configure the plugin.
1. The rest of the settings are all similar to the V1 module settings. We recommend to use the same settings as used in V1 module, with the only difference that "clientId" is referenced as "storeId" in V2 module.[View V1 Configuration documentation](https://amzn.github.io/amazon-payments-magento-2-plugin/configuration.html).

## Alexa Notifications

The Alexa Notifications feature lets you provide shipment tracking information to Amazon Pay, for the Amazon Pay orders, so that Amazon Pay can notify your customers on their Alexa device when shipments are out for delivery, as well as when they are delivered.

Click [here](https://developer.amazon.com/docs/amazon-pay-onetime/delivery-notifications.html#heres-what-your-customer-will-experience) to listen to the customer experience.

Alexa Notifications feature is inbuilt with the Amazon Pay checkout Version 2 module. There are not additional keys required to activate this feature. Once you have enabled Alexa Notifications, your store is ready to use this feature.

Alexa Delivery Notifiaction API is called when:

- A shipment is submitted with the carrier code, name and tracking number
- On a successful API call, you will see its status as ‘Amazon Pay has received shipping tracking information for carrier <carrier_name> and tracking number <tracking_number>’.

The status will show under:
* ‘Comments History’ in the Order view.
* Under individual Shipment -> Shipment History.

## Branch information

The following table provides an overview on which Git branch is compatible to which Magento 2 version. The last column "Latest release" refers to the latest version of our extension that is compatible to the Magento 2 version in the first column. 

| Magento Version | Github Branch | Latest release |
| ------------- | ------------- | ------------- |
| 2.2.6 - 2.3.x | [V2checkout-1.2.x](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout-1.2.x) | 1.7.0 |
| 2.4.0 and above | [V2checkout](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout) | 2.3.0 |
