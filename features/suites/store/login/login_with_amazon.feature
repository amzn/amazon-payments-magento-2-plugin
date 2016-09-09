Feature: As a customer
  I need to login with amazon
  So that I can pay with my amazon account

  @javascript
  Scenario: login with amazon button is available on the login page
    When I go to login
    Then I see a login with amazon button on the login page

  @javascript
  Scenario: login with amazon button is available on the registration page
    When I go to register
    Then I see a login with amazon button on the registration page

  @javascript
  Scenario: login with amazon button is available on the basket page
    Given there is a valid product in my basket
    When I go to my basket
    Then I see a login with amazon button on the basket page

  @javascript
  Scenario: login with amazon creates customer account linked to amazon account
    Given there is a not a customer "amazoncustomer@example.com"
    When I login with amazon as "amazoncustomer@example.com"
    Then a customer "amazoncustomer@example.com" should have been created
    And "amazoncustomer@example.com" is associated with an amazon account
    And I should be logged in as a customer

  @javascript
  Scenario: login with amazon logs in customer account that is already linked to amazon
    Given there is a customer "amazoncustomer@example.com" which is linked to amazon
    When I login with amazon as "amazoncustomer@example.com"
    Then I should be logged in as a customer

  @javascript
  Scenario: login with amazon checks password for customer account that has a matching email but is not linked with amazon
    Given there is a customer "amazoncustomer@example.com"
    And "amazoncustomer@example.com" has never logged in with amazon
    When I login with amazon as "amazoncustomer@example.com"
    Then I should not be logged in as a customer
    And I should be asked to confirm my password
    When I confirm my password
    Then I should be logged in as a customer

  @javascript
  Scenario: login with amazon links amazon account to customer account if already logged in
    Given there is a customer "magentocustomer@example.com"
    And "magentocustomer@example.com" has never logged in with amazon
    And "magentocustomer@example.com" is logged in
    And there is a valid product in my basket
    When I login with amazon on the basket page as "amazoncustomer@example.com"
    Then "magentocustomer@example.com" is associated with an amazon account