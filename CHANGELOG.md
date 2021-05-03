# Change Log

## 5.3.0
* Added sort order to payment method config
* Changed the REST requests to pass in the Amazon Checkout Session ID instead of the cart ID
* Fixed bug where multiple url parameters would cause reloading in checkout
* Fixed bug with the way serializer was called in Alexa notification processing
* Fixed incorrect exception messaging in Alexa notification
* Removed quote to Amazon Session mapping table
* Updated how javascript customizations are implemented
* Updated the cart and success redirect behavior to be configurable

## 5.2.0
* Added Sign in with Amazon to the authentication modal
* Fixed bug where pressing enter on a text input config field would open the file selector for Amazon Private Key
* Fixed usage of a php8 str_contains so that installations that don't have the Symfony polyfill will still work correctly
* Fixed issue that crawlers could trigger by hitting the login/checkout path without an Amazon checkout session
* Modified Amazon Pay button rendering so that it will be triggered by customer data loading after the Amazon javascript runs
* Updated MFTF tests to handle authentication popup that happens in desktop view now
* Updated composer.json requires to specify a few additional dependencies that are used

## 5.1.0
* General availability release
* Added tests for refund, multi auth refund, and multi auth with capture initiated
* Added Japanese translations and updates translations for other languages
* Change to use the button page URL as the redirect back when cancelling the session.
* Fixed bug with updating configuration without changing the private key.
* Fixed customer data not getting cleared when getting signed in via Amazon Pay checkout
* Replaced PHP8 only function being used for a more compatible one.
* Updated Alexa feature name
* Updated private key field to also allow usage of a .pem file.
* Updated the platform_id for the new module version

## 5.0.1
* Removed reliance on legacy config value being set.

## 5.0.0
* Beta release, replacing all versions that were included as part of the "Vendor Bundled Extension" (VBE) program in previous Magento releases.
* Added input validation and test upon saving for credentials.
* Changed calling `closeChargePermission` instead of `cancelCharge` when voiding an order.
* Changed the button to create the session directly instead of through Magento.
* Fixed creating a credit memo against a split capture invoice.
* Fixed loading correct config when switching store view before a cart is initiated.
* Fixed product page button hover and tooltip button placement.
* Removed redirect to cart on login if the customer has products in cart.
