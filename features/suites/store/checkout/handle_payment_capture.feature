Feature: As an admin
  I need to capture payment for an amazon order
  So that I can receive payment for goods

  Background:
    Given I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account
    And I place my order

  @javascript
  Scenario: admin captures payment for an order
    Given I am logged into admin
    And I go to invoice the last order for "amazoncustomer@example.com"
    When I submit my invoice
    Then there should be a paid invoice for the last order for "amazoncustomer@example.com"
    And amazon should have a complete capture for the last order for "amazoncustomer@example.com"
    And there should be a closed capture for the last order for "amazoncustomer@example.com"
    And there should be a closed authorization for the last order for "amazoncustomer@example.com"
