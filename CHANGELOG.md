# Change Log

## 5.0.0
* General Availability release, replacing versions that were previously included in core Magento releases
* Fixed creating a credit memo against a split capture invoice
* Added input validation and test upon saving for credentials
* Changed calling `closeChargePermission` instead of `cancelCharge` when voiding an order
* Fixed loading correct config when switching store view before a cart is initiated
* Changed the button to create the session directly instead of through Magento
* Removed redirect to cart on login if the customer has products in cart
* Fixed product page button hover and tooltip button placement
