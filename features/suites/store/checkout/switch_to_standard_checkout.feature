Feature: As a customer
  I need to be able to switch to the standard checkout
  So that I can pay without using my amazon account

  Background:
    Given I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method

  @javascript
  Scenario: Amazon customer reverts to standard checkout
    When I revert to standard checkout
    Then the standard shipping form should be displayed
    And the basket for "amazoncustomer@example.com" should not be linked to an amazon order

