@register-and-enable-user
Feature: Process new users
    In order to add new users into the application
    As an application user
    I need to be able to register new user, enable and login this user

    Scenario: Register new user
        Given the "Content-Type" request header is "application/json"
        Given the graphql request body is:
        """
        mutation {
              register(
                  name: "test",
                  email: "test@gmail.com",
                  password: "testtest",
                  password_confirmation: "testtest"
              )
              {
                  status,
                  message
              }
          }
        """
        When I request "/graphql" using HTTP POST
        Then the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
              "register": {
                  "status": "REGISTERED",
                  "message": "Successfully registered"
              }
          }
        }
        """

        # Try to register user with the same details
        Given the graphql request body is:
        """
        mutation {
              register(
                  name: "test",
                  email: "test@gmail.com",
                  password: "testtest",
                  password_confirmation: "testtest"
              )
              {
                  status,
                  message
              }
          }
        """
        When I request "/graphql" using HTTP POST
        And the response body contains JSON:
        """
        {
          "errors": [
            {
                "messages": [
                    "The email has already been taken.",
                    "The name has already been taken."
                ],
                "code": 400
            }
          ]
        }
        """

    Scenario: Enable new user by email
        Given i get confirmation token by "test@gmail.com" email
        And I save it into "confirmationToken"
        Given the "Content-Type" request header is "application/json"
        Then the graphql request body is:
        """
        mutation {
              confirm_registration(
                  token: "<<confirmationToken>>"
              )
              {
                  confirmed
              }
          }
        """
        When I request "/graphql" using HTTP POST
        Then the response code is 200
        And the response body contains JSON:
        """
        {
            "data": {
                "confirm_registration": {
                    "confirmed": true
                }
            }
        }
        """

    Scenario: Login new user by login and email
        Given the "Content-Type" request header is "application/json"
        Then the graphql request body is:
        """
        mutation {
              login(
                  email: "test@gmail.com",
                  password: "testtest",
              )
              {
                  token_type,
                  access_token
              }
          }
        """
        When I request "/graphql" using HTTP POST
        Then the response code is 200
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

