@check-auth-user
Feature: Get authenticated user
    In order to use application
    As an application user
    I need to be able to get authenticated user

    Scenario: Get logged in user
        Given the "Content-Type" request header is "application/json"
        Then the graphql request body is:
        """
        mutation {
              login(
                  email: "behat@thewayyougo.com",
                  password: "behatpass",
              )
              {
                  token_type,
                  access_token
              }
          }
        """
        When I request "/graphql" using HTTP POST
        Then I get auth token from the response
        And I save it into "authToken"
        Given the "Authorization" request header is "Bearer <<authToken>>"
        And the response code is 200
        And the response body contains JSON:
        """
        {
            "data": {
                "login": {
                    "token_type": "Bearer",
                    "access_token": "@variableType(string)"
                }
            }
        }
        """

        # Get currently authenticated user
        Then the graphql request body is:
        """
        query{
          auth{
            name
            email
            enabled
            created_at
          }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "auth": {
              "name": "behat",
              "email": "behat@thewayyougo.com",
              "enabled": true,
              "created_at": "@variableType(string)"
            }
          }
        }
        """

        # Logout currently authenticated user
        Given the graphql request body is:
        """
        mutation{
          logout{
            code
            message
          }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "logout": {
              "code": "200",
              "message": "Successfully logged out"
            }
          }
        }
        """
