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
* Magento version requirement: 2.4.0 (June 2020 release)
* PHP 7.3 and 7.4 supported

## Dependencies

You can find a list of modules in the require section of the `composer.json` file located in the
same directory as this `README.md` file.

## Installation and Configuration
This section will be released once Magento 2.4.0 is widely available on July 28th 2020.
If you are on M2.2.6 and above, please use the [V2checkout-1.2.x](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout-1.2.x) branch. The [README.md](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout-1.2.x#amazon-pay-checkout-v2) contains all the installation and configuration information.

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
| 2.2.6 - 2.3.x | [V2checkout-1.2.x](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout-1.2.x) | 1.4.0 |
| 2.4.0 and above | [V2checkout](https://github.com/amzn/amazon-payments-magento-2-plugin/tree/V2checkout) | 2.1.3 |
