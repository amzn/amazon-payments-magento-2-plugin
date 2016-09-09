Feature: As a customer
  I want to register with Amazon after I have purchased something using Amazon Payment

  Background:
    Given Login with Amazon is disabled

  @javascript
  Scenario:
    Given there is a valid product in my basket
    And I login with amazon on the basket page as "amazoncustomer@example.com"
    And I go to the checkout
    And I provide the "amazoncustomer@example.com" email in the shipping form
    And I select a shipping address from my amazon account
    And I select a valid shipping method
    And I go to billing
    And I place my order
    And I can create a new Amazon account on the success page with email "amazoncustomer@example.com"
    Then "amazoncustomer@example.com" is associated with an amazon account
