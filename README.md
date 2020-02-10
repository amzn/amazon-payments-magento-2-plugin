# Amazon_PayV2 Module

The Amazon_PayV2 module provides the "Amazon Pay" payment method using version 2 of the Amazon Pay API.

## What's new in Pay V2?

*Modular Accelerated Checkout* places the Amazon shipping and payment widgets outside the Magento checkout 
template system, improving usability and transaction error handling.  

## About Amazon Pay and Login

Amazon Pay and Login provides integration of your Magento 2 store with Amazon Pay and Login 
with Amazon services. This helps your customers shop quickly, safely and securely. 
Your customers can pay on your website without re-entering their payment and address details. 
All Amazon Pay transactions are protected by Amazon's A-to-z Guarantee.

## Installation Steps

Refer [here](https://gist.github.com/tarishah/9b12146925eb9b5dbe5a1a3936b9b382) for the V2 Checkout Installation steps.

## PWA Support
1. The V2 module exposes the REST endpoints that needs to set up. You can find them at [src/PayV2/etc/webapi.xml](https://github.com/amzn/amazon-payments-magento-2-plugin/blob/V2checkout/src/PayV2/etc/webapi.xml)
1. The front end needs to be setup by the merchant/developer.


## Dependencies

You can find a list of modules in the require section of the `composer.json` file located in the
same directory as this `README.md` file.

## Extension Points

Amazon Pay does not provide any specific extension points.

## Additional Information

[View the Complete User Guide](https://amzn.github.io/amazon-payments-magento-2-plugin/)
