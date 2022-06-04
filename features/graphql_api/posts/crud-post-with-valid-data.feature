@api-posts @crud-post-with-valid-data
Feature: Create post
    In order to use application
    As an application user
    I need to be able to create post

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


        # Create new post
        Then the graphql request body is:
        """
        mutation{
            createPost(title: "Test post",
            description: "Test post description",
            short_description:"Test post short description",
            type: normal)
            {
                    _id
                    title
                    short_description
                    description
                    type
                    isLikedByAuthUser
                    isBookmarkedByAuthUser
                    created_at
                    user{
                        name
                        email
                    }
                    likes{
                        user{
                            name
                            email
                        }
                   }
            }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And I get new post ID
        And I save it into "postId"
        And the response body contains JSON:
        """
        {
          "data": {
            "createPost": {
              "_id": "<<postId>>",
              "title": "Test post",
              "short_description": "Test post short description",
              "description": "Test post description",
              "type": "normal",
              "isLikedByAuthUser": false,
              "isBookmarkedByAuthUser": false,
              "created_at": "@variableType(string)",
              "user": {
                "name": "behat",
                "email": "behat@thewayyougo.com"
              },
              "likes": []
            }
          }
        }
        """

        # Update post by ID
        Then the graphql request body is:
        """
        mutation{
            updatePost(
            _id:"<<postId>>",
            title: "Test post Updated",
            description: "Test post description Updated",
            short_description:"Test post short description Updated",
            type: tweet)
            {
                    _id
                    title
                    short_description
                    description
                    type
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
            "updatePost": {
              "_id": "<<postId>>",
              "title": "Test post Updated",
              "short_description": "Test post short description Updated",
              "description": "Test post description Updated",
              "type": "tweet",
              "created_at": "@variableType(string)"
            }
          }
        }
        """

         # Delete post by ID
        Then the graphql request body is:
        """
        mutation{
            deletePost(_id:"<<postId>>")
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
            "deletePost": {
              "_id": "<<postId>>"
            }
          }
        }
        """
