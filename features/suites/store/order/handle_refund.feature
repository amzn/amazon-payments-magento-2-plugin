Feature: As an admin
  I need to refund an amazon order
  So that I can give customers their money back

  Background:
    Given I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account
    And I place my order
    And I am logged into admin
    And I go to invoice the last order for "amazoncustomer@example.com"
    And I submit my invoice

  @javascript
  Scenario: admin refunds order
    Given I go to refund the last invoice for "amazoncustomer@example.com"
    When I submit my refund
    Then there should be a credit memo for the value of the last invoice for "amazoncustomer@example.com"
    And amazon should have a refund for the last invoice for "amazoncustomer@example.com"