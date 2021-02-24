# Change Log

## <new release>
* Adds tests for refund, multi auth refund, and multi auth with capture initiated
* Updates Alexa feature name
* Adds Japanese translations and updates translations for other languages

## 5.0.1
* Remove reliance on legacy config value being set.

## 5.0.0
* Beta release, replacing all versions that were included as part of the "Vendor Bundled Extension" (VBE) program in previous Magento releases.
* Fixed creating a credit memo against a split capture invoice.
* Added input validation and test upon saving for credentials.
* Changed calling `closeChargePermission` instead of `cancelCharge` when voiding an order.
* Fixed loading correct config when switching store view before a cart is initiated.
* Changed the button to create the session directly instead of through Magento.
* Removed redirect to cart on login if the customer has products in cart.
* Fixed product page button hover and tooltip button placement.
