# Change Log

## <new release>
* Uses the button page URL as the redirect back when cancelling the session. Provides tests for it.

## 5.1.0
* Added tests for refund, multi auth refund, and multi auth with capture initiated
* Added Japanese translations and updates translations for other languages
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
