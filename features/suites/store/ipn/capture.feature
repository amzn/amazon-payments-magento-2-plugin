Feature: As a store
  I want to receive notifications about captures

  Background:
    Given IPN is enabled
    And I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account

  @javascript
  Scenario: Completed capture notification
    Given I am requesting authorization for a capture that will be pending then successful
    And I place my order
    And I am logged into admin
    And I go to invoice the last order for "amazoncustomer@example.com"
    When I submit my invoice
    Then I should be notified that my capture is pending
    And the last invoice for "amazoncustomer@example.com" should be pending
    And the last capture transaction for "amazoncustomer@example.com" should be open
    And the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a capture complete IPN for "amazoncustomer@example.com"'s last order
    Then there should be a paid invoice for the last order for "amazoncustomer@example.com"
    And there should be a closed capture for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should have the processing state

  @javascript
  Scenario: Declined capture notification
    Given I am requesting authorization for a capture that will be pending then declined
    And I place my order
    And I am logged into admin
    And I go to invoice the last order for "amazoncustomer@example.com"
    When I submit my invoice
    Then I should be notified that my capture is pending
    And the last invoice for "amazoncustomer@example.com" should be pending
    And the last capture transaction for "amazoncustomer@example.com" should be open
    And the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a capture declined IPN for "amazoncustomer@example.com"'s last order
    Then the last invoice for "amazoncustomer@example.com" should be cancelled
    And there should be a closed capture for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should be on hold
