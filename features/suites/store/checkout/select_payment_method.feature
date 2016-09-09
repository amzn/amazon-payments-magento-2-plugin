Feature: As a customer
  I need to select a payment method
  So that I can pay for my order

  @javascript
  Scenario: customer logged into amazon sees payment widget
    Given I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    When I go to billing
    Then the amazon payment widget should be displayed

  @javascript
  Scenario: customer not logged into amazon sees other payment methods
    Given there is a valid product in my basket
    And I go to the checkout
    And I provide the "guestcustomer@example.com" email in the shipping form
    And I provide a valid shipping address
    And I select a valid shipping method
    When I go to billing
    Then the amazon payment widget should not be displayed
    And I should be able to select a payment method

  @javascript
  Scenario: billing address is selected from amazon
    Given I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account
    When I place my order
    Then the last order for "amazoncustomer@example.com" should have my amazon billing address
