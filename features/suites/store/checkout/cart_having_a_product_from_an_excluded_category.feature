Feature: As an admin
  I want to configure a list of product categories that should not allow an option to pay with Amazon
  So that merchants can choose not to show the Amazon payment option for certain products

  @javascript
  Scenario: PWA button is not shown in a Product Page in an excluded Category
    Given Product SKU "excluded-product" belongs to an excluded category
    And I go to the Product Page of product SKU "excluded-product"
    Then the PWA button should not be visible

  @javascript
  Scenario: PWA button is not shown in the minicart if Cart contains a Product from an excluded Category
    Given Product SKU "excluded-product" belongs to an excluded category
    And Product SKU "excluded-product" is added to the basket
    Then the minicart should not display the PWA button

  @javascript
  Scenario: PWA button is not shown in the minicart if Cart contains a Product from an excluded Category
    Given Product SKU "excluded-product" belongs to an excluded category
    And Product SKU "excluded-product" is added to the basket
    Then the minicart should not display the PWA button

  @javascript
  Scenario: PWA button is not shown in the Basket page if Cart contains a Product from an excluded Category
    Given Product SKU "excluded-product" belongs to an excluded category
    And Product SKU "excluded-product" is added to the basket
    When I go to my basket
    Then the Basket page should not display the PWA button

  @javascript
  Scenario: PWA button is not shown in the Checkout page if Cart contains a Product from an excluded Category
    Given Product SKU "excluded-product" belongs to an excluded category
    And Product SKU "excluded-product" is added to the basket
    When I go to the checkout
    Then I do not see a pay with amazon button
