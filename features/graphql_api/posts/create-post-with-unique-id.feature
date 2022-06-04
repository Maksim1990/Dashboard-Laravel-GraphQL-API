@api-posts @create-post-with-unique-id
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


        # Create new post with unique ID
        Then the graphql request body is:
        """
        mutation{
            createPost(title: "Test post",
            description: "Test post description",
            short_description:"Test post short description",
            type: normal,
            unique_id: "12345678910"
            )
            {
                    _id
            }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "createPost": {
              "_id": "12345678910"
            }
          }
        }
        """

        # Delete post by ID
        Then the graphql request body is:
        """
        mutation{
            deletePost(_id:"12345678910")
            {
                    _id
            }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
