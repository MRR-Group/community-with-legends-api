Feature: Example test
    Scenario: User can access to root page
    Given a user is requesting "/" using GET method
    When a request is sent
    Then the response should have status 200 and contain JSON with key "message" and value "Welcome"
