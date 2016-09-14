Feature: As a customer
  I need to have my order confirmed when it's authorized a payment from my amazon account

  Background:
    Given I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account

  @javascript
  Scenario: customer order is confirmed
    Given I place my order
    Then "amazoncustomer@example.com" should have placed an order
    And the order for "amazoncustomer@example.com" should be confirmed
