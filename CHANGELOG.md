# Change Log

## 5.16.1
* Remove platform/module version information from button payloads
* Fix admin display bug regarding upgrading legacy keys when a CV2 public key ID already exists
* Fixed invoice comment noise with Automatic Authorization Mode

## 5.16.0
* Added support for subscriptions, requires the ParadoxLabs Adaptive Subscription module
* Added platformid for APB payload
* Fixed phpcs warnings for php 8
* Fixed issue with installing the module when there is no existing CV1 config
* Fixed issue when an Allowed Country was somehow saved with an empty string

## 5.15.0
* Added Key Upgrade feature (automatically upgrade CV1 keys to CV2 if updating from legacy module)
* Fixed backward compatibility with Zend availability
* Fixed render issue with multiple Amazon Sign In buttons on the same page

## 5.14.3
* Fixed PHP 7 compatibility

## 5.14.2
* Removed usage of Zend libraries and updated requirements for PHP 8.2/Magento 2.4.6 compatibility
* Fixed issue where coupon usage is not properly decremented when placing order fails

## 5.14.1
* Changed how buttons are rendered for compatibility with estimated order amount feature
* Removed estimated order amount from PDP button

## 5.14.0
* Added configurable options for checkout and signin cancel return urls
* Added estimated order amount to the button payload
* Fixed issue with ExceptionLogger using a removed method
* Fixed issue with customer names having characters that Magento doesn’t allow
* Fixed issue with APB and virtual orders when taxes are applied
* Improved compatibility with b2b modules and address display
* Updated config to not show Auto Key Exchange button for JPY as it is not supported

## 5.13.1
* Fixed issue with invalid array reference if a transaction doesn’t have a charge permission
* Fixed issue with GraphQL config query supporting omitPayloads
* Fixed issue with switching stores to refresh the Amazon Pay button config

## 5.13.0
* Added Graphql support
* Added endpoints to fetch individual config types
* Change how buttons are rendered so they are not blocked waiting for config
* Fixed an error when using the REST complete endpoint with a declined card
* Updated some translations

## 5.12.0
* Change to display billing address for US/JP regions to match EU/UK
* Fixed a regression with restricted categories
* Fixed an issue where a quote tied to a canceled order couldn’t be re-used with Amazon Pay
* Fixed an issue where the billing address would not be shown (thanks @ptylek!)
* Fixed compatibility with jquery-migrate that doesn’t like self-closing tags
* Updated some strings and translations

## 5.11.1
* Fixed an issue where autoloader is needed to detect version of phpseclib used

## 5.11.0
* Added compatibility with Adobe Commerce / Magento Open Source 2.4.4
* Fixed an issue with email population
* Updated validation on Private Key field to allow SANDBOX- or LIVE- prefixes (thanks @cmorrisonmvnt!)

## 5.10.0
* Added signin REST endpoint
* Fixed an issue that could occur when rendering the Amazon Pay button more than once
* Fixed an issue with configuring payment methods at a store scope when the admin user doesn’t have access to the default scope (thanks @barbazul!)
* Fixed an issue with configuration wizard executed in a store where the admin doesn’t have access to the default store
* Fixed MFTF tests to allow for different flow on the Amazon authentication popup
* Updated to allow partial/split capture in EU/UK regions
* Updated REST endpoints to allow loading session from the user context instead of passing masked cart ID

## 5.9.1
* Fixed issue with umlauts in PayNow button flow
* Updated config labels for Magento Checkout redirect paths

## 5.9.0
* Added custom Carrier Code mapping
* Added config options to allow headless integrations to change return urls
* Changed validation on private key to allow empty values
* Fixed issue with processing an invalid Amazon response
* Fixed issue with One Step Checkouts having stale data in the Payment Methods button

## 5.8.0
* Added log message if we are unable to complete checkout session due to an existing order with same quoteId
* Added email when asynchronous order processing is declined
* Fixed issue with Magento Open Source when configured to only allow a single address line
* Fixed API output for config endpoint to return key/value pairs
* Fixed issue generating Swagger docs (thanks @ebaschiera!)
* Fixed issue with canceling transactions started prior to upgrading to CV2/Marketplace module
* Fixed issue where the Amazon Pay payment method button on Onestepcheckout_Iosc would not trigger when clicking Place Order

## 5.7.1
* Fixed issue when phone number not required and entered in Magento
* Updated API calls to take in a masked cart ID so they can be used without relying on Magento sessions
* Updated logging to sanitize some data

## 5.7.0
* Changed the response of completeCheckoutSession API call to include both increment ID and order ID
* Fixed issue with logging in when a customer has an empty password hash (thanks @rafczow!)
* Fixed issue with sending too many decimal points to Amazon API, particularly an issue when using TaxJar as it uses more decimal points than Magento typically does (thanks @vkalchenko!)
* Fixed issue where the Address Form would not be shown even though Amazon address did not provide a needed field, particularly State/Province
* Fixed issue where using Amazon Pay in the Payment Methods section did not work on one step checkouts
* Fixed issue where using Amazon Pay in the Payment Methods section could bypass agreeing to Terms and Conditions
* Removed usage of isPlaceOrderActionAllowed in js components
* Updated response validators to look for specific response code and states

## 5.6.0
* Changed the merchantReferenceId to be set on the charge permission after the order is completed
* Changed IPN handling so that it wouldn’t re-try capture on duplicate messages
* Changed flow so any changes at billing step route back to shipping details as address could have changed
* Fixed issue where only the first invoice created would capture payment
* Fixed issue where sometimes the payment method isn’t set on the payment if the PayNow button is used
* Fixed issue where the street on German addresses could get set twice
* Fixed issue with the mobile tooltip being truncated

## 5.5.1
* Add url to csp_whitelist.xml
* Fix issue with the payment method button and UK addresses

## 5.5.0
* Added Auto Key Exchange for configuring the credentials
* Added PayNow functionality for the button on the final step of checkout
* Added more logging to the Alexa feature
* Added more logging to capturing flow
* Added validation that Amazon API always returns a buyerId
* Changed Sandbox config to be available at store view scope
* Fixed admin stylesheets for non-US locale
* Fixed issue with one Amazon account logging into multiple stores (thanks @flaviy!)
* Fixed race condition that could happen when re-drawing the button

## 5.4.0
* Fixed credential validation when inheriting from parent scope
* Fixed issue to properly handle when Amazon Pay returns empty buyer ID
* Fixed issue with using Alexa notifications and custom carriers
* Fixed issue where a quote could be submitted to Magento multiple times

## 5.3.0
* Support for OneStepCheckout v1.2.047+
* Added sort order to payment method config
* Changed the REST requests to pass in the Amazon Checkout Session ID instead of the cart ID
* Fixed bug where multiple url parameters would cause reloading in checkout
* Fixed bug with the way serializer was called in Alexa notification processing
* Fixed incorrect exception messaging in Alexa notification (thanks @dmitriyklyuzov!)
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
