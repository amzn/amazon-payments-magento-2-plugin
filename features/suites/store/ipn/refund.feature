Feature: As a store
  I want to receive notifications about refunds

  Background:
    Given IPN is enabled
    And I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account

  Scenario: Declined refund notification
    Given I am requesting authorization for a refund that will be declined
    And I place my order
    And I am logged into admin
    And I go to invoice the last order for "amazoncustomer@example.com"
    And I submit my invoice
    And I go to refund the last invoice for "amazoncustomer@example.com"
    When I submit my refund
    When I receive a refund declined IPN for "amazoncustomer@example.com"'s last order
    Then there should be an admin notification that the last refund for "amazoncustomer@example.com"  was declined

