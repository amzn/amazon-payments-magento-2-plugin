Feature: As a customer
  I can only login and pay with amazon if I am using a supported currency

  @javascript
  Scenario:
    Given I want to pay using an unsupported currency
    And there is a valid product in my basket
    When I go to the checkout
    Then I do not see a pay with amazon button
