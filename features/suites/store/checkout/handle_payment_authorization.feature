Feature: As a customer
  I need to authorize a payment from my amazon account
  So that I can pay for goods

  Background:
    Given I login with amazon as "amazoncustomer@example.com"
    And there is a valid product in my basket
    And I go to the checkout
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I select a payment method from my amazon account

  @javascript
  Scenario: customer authorizes payment for an order
    When I place my order
    Then "amazoncustomer@example.com" should have placed an order
    And there should be an open authorization for the last order for "amazoncustomer@example.com"
    And amazon should have an open authorization for the last order for "amazoncustomer@example.com"

  @javascript
  Scenario: customer authorizes payment for an order charged on order placement
    Given orders are charged for at order placement
    When I place my order
    Then "amazoncustomer@example.com" should have placed an order
    And there should be a closed capture for the last order for "amazoncustomer@example.com"
    And there should be a paid invoice for the last order for "amazoncustomer@example.com"
    And amazon should have a complete capture for the last order for "amazoncustomer@example.com"

  @javascript
  Scenario: customer authorizes payment for an order in asynchronous mode
    Given orders are authorized asynchronously
    When I place my order
    Then "amazoncustomer@example.com" should have placed an order
    And there should be an open authorization for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should be in payment review

  @javascript
  Scenario: customer authorizes payment for an order that will timeout in synchronous then asynchronous mode
    Given orders are authorized synchronously then asynchronously
    And I am requesting authorization on a payment that will timeout
    When I place my order
    Then "amazoncustomer@example.com" should have placed an order
    And there should be an open authorization for the last order for "amazoncustomer@example.com"
    And the last order for "amazoncustomer@example.com" should be in payment review