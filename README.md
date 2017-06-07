# Amazon Pay and Login with Amazon for Magento 2

[View the Complete User Guide](https://amzn.github.io/amazon-payments-magento-2-plugin)

## Learn More about Amazon Pay
* [US] (https://pay.amazon.com/us/sp/magento)
* [UK] (https://pay.amazon.com/uk/sp/magento)
* [DE] (https://pay.amazon.com/de/sp/magento)
* [FR] (https://pay.amazon.com/fr/sp/magento)
* [IT] (https://pay.amazon.com/it/sp/magento)
* [ES] (https://pay.amazon.com/es/sp/magento)


## Pre-Requisites
* Magento 2.1+
    * [Magento 2 System Requirements](http://devdocs.magento.com/magento-system-requirements.html)
* SSL is installed on your site and active on Checkout and Login pages
* A verified Amazon Payments merchant account

## Installation and Configuration

Please follow the instructions in the [User Guide](https://amzn.github.io/amazon-payments-magento-2-plugin)

## Release Notes
### v1.1.1 stability
#### Enhancements:
        * Display the module version in the admin html
        * Support for Modernizr 3.x
        * Added extended support for japanese names
        * Removed disturbing message for charge on order
        * Configuration option to supply the store name added

#### Bug Fixes:
        * Order handling for free orders corrected
        * Fixed incompatibility on CompleteOrder
        * Displayed URLs did not take the store-view configured domain into account
        * Removed unneeded CSS and layout
        * jQuery storage API not always present

### v1.1.0 Rebrand
#### Enhancements:
        * This release does not add any new features

#### Bug Fixes:
        * This release does not fix any bugs

### v1.0.10 Small fixes
#### Enhancements:
   * #31 Scope parameter propagated to the Amazon Pay widgets
   * #11 Integrate checkout agreement blocks in the checkout

#### Bug Fixes:
   * #27 Get the payment method from the order instead of the quote
   * #33 Fix the checkout layout block to be in line with Magento 2.1
