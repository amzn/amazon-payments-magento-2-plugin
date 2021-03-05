# Change Log

## <new release>
* Added tests for refund, multi auth refund, and multi auth with capture initiated
* Updated Alexa feature name
* Added Japanese translations and updates translations for other languages
* Updated the platform_id for the new module version
* Fixed bug with updating configuration without changing the private key.
* Fixed customer data not getting cleared when getting signed in via Amazon Pay checkout
* Replaced PHP8 only function being used for a more compatible one.

## 5.0.1
* Removed reliance on legacy config value being set.

## 5.0.0
* Beta release, replacing all versions that were included as part of the "Vendor Bundled Extension" (VBE) program in previous Magento releases.
* Fixed creating a credit memo against a split capture invoice.
* Added input validation and test upon saving for credentials.
* Changed calling `closeChargePermission` instead of `cancelCharge` when voiding an order.
* Fixed loading correct config when switching store view before a cart is initiated.
* Changed the button to create the session directly instead of through Magento.
* Removed redirect to cart on login if the customer has products in cart.
* Fixed product page button hover and tooltip button placement.
