@api-posts @crud-post-with-invalid-data
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


        # Create new post without title
        Then the graphql request body is:
        """
        mutation{
            createPost(
            description: "Test post description",
            short_description:"Test post short description",
            type: normal
            )
            {
                    _id
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
                "The title field is required."
                ],
               "code": 400
            }
          ]
        }
        """

        # Create new post without description
        Then the graphql request body is:
        """
        mutation{
            createPost(
            short_description:"Test post short description",
            type: normal
            )
            {
                    _id
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
                "The title field is required.",
                "The description field is required."
               ],
               "code": 400
            }
          ]
        }
        """
