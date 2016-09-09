Feature: As a store
  I don't want to receive notifications if IPN is disabled

  Background:
    Given IPN is disabled
    And orders are authorized asynchronously
    And I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account

  Scenario: Completed authorization notification
    When I place my order
    Then a authorization open IPN for "amazoncustomer@example.com"'s last order should be rejected