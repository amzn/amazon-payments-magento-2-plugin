# Amazon_PayV2 Module

The Amazon_PayV2 module provides the "Amazon Pay" payment method using version 2 of the Amazon Pay API.

## What's new in Amazon Pay for Magento 2?

*Modular Accelerated Checkout* places the Amazon shipping and payment widgets outside the Magento checkout 
template system, improving usability and transaction error handling.  

## About Amazon Pay for Magento 2

Amazon Pay for Magento 2 provides integration of your Magento 2 store with Amazon Pay for Magento 2 
with Amazon services. This helps your customers shop quickly, safely and securely. 
Your customers can pay on your website without re-entering their payment and address details. 
All Amazon Pay transactions are protected by Amazon's A-to-z Guarantee.

## Dependencies

You can find a list of modules in the require section of the `composer.json` file located in the
same directory as this `README.md` file.

## Prerequisite for installation

Before installing Amazon Pay, please remove any previously installed versions. This includes all directories 
and their files from the following paths:

* `app/code/Amazon/PayV2`
* `vendor/amzn/amazon-pay-and-login-with-amazon-core-module`
* `vendor/amzn/amazon-pay-module`
* `vendor/amzn/login-with-amazon-module`

If you are using composer to install Amazon Pay, it will remove the files in `vendor` for you, but you will 
need to manually clean `app/code/Amazon` if present.

## Installation

The extension is available via composer in Packagist or Magento Marketplace as `amzn/amazon-pay-magento-2-module`.

## Extension Points

Amazon Pay does not provide any specific extension points.

## Additional Information

[View the Complete User Guide](https://amzn.github.io/amazon-payments-magento-2-plugin/)
