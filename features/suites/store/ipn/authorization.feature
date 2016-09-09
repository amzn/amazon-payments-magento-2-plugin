Feature: As a store
  I want to receive notifications about authorizations

  Background:
    Given IPN is enabled
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
    Then the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a authorization open IPN for "amazoncustomer@example.com"'s last order
    Then the last order for "amazoncustomer@example.com" should have the processing state

  Scenario: Completed authorization and capture notification
    Given orders are charged for at order placement
    When I place my order
    Then the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a authorize and capture complete IPN for "amazoncustomer@example.com"'s last order
    Then there should be a paid invoice for the last order for "amazoncustomer@example.com"
    And there should be a closed capture for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should have the processing state

  Scenario: Soft declined authorization notification
    Given I am requesting authorization on a payment that will use an invalid method
    When I place my order
    Then the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a authorization soft declined IPN for "amazoncustomer@example.com"'s last order
    Then there should be a closed authorization for the last order for "amazoncustomer@example.com"
    When I receive a order payment open IPN for "amazoncustomer@example.com"
    Then there should be an open authorization for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should have the processing state

  Scenario: Soft declined authorization and capture notification
    Given I am requesting authorization on a payment that will use an invalid method
    And orders are charged for at order placement
    When I place my order
    Then the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a authorize and capture soft declined IPN for "amazoncustomer@example.com"'s last order
    Then there should be a closed capture for the last order for "amazoncustomer@example.com"
    When I receive a order payment open IPN for "amazoncustomer@example.com"
    Then there should be a paid invoice for the last order for "amazoncustomer@example.com"
    And there should be a closed capture for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should have the processing state

  Scenario: Hard declined authorization notification
    Given I am requesting authorization on a payment that will be rejected
    When I place my order
    Then the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a authorization hard declined IPN for "amazoncustomer@example.com"'s last order
    Then there should be a closed authorization for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should be on hold

  Scenario: Hard declined authorization and capture notification
    Given I am requesting authorization on a payment that will be rejected
    And orders are charged for at order placement
    When I place my order
    Then the last order for "amazoncustomer@example.com" should be in payment review
    When I receive a authorize and capture hard declined IPN for "amazoncustomer@example.com"'s last order
    Then the last invoice for "amazoncustomer@example.com" should be cancelled
    And there should be a closed capture for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should be on hold