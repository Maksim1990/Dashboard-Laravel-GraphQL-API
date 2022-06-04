@api-posts @get-post-and-all-posts
Feature: Get post and all posts
    In order to use application
    As an application user
    I need to be able to get post and all posts

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

        # Get post with invalid ID
        Then the graphql request body is:
        """
        query{
          post(_id:"INVALID_ID"){
            _id
            title
            description
            type
          }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "post": null
          }
        }
        """

        # Get post with valid ID
        Then the graphql request body is:
        """
        query{
          post(_id:"<<postId>>"){
            _id
            title
            description
            type
          }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "post": {
              "_id": "<<postId>>",
              "title": "Test post",
              "description": "Test post description",
              "type": "normal"
            }
          }
        }
        """

        # Get post with valid ID
        Then the graphql request body is:
        """
        query{
          posts(
            input:{orderBy:{field:"create_at",order:ASC},
            params:{type:normal, onlyBookmarks:false} },
            first:10,
            page:0
          ){
            data{
              _id
              title
              description
              type
            }
            paginatorInfo{
                  count
                  currentPage
                  firstItem
                  hasMorePages
                  lastItem
                  lastPage
                  perPage
                  total
                }
          }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "posts": {
              "data": [
                {
                  "_id": "<<postId>>",
                  "title": "Test post",
                  "description": "Test post description",
                  "type": "normal"
                }
              ],
              "paginatorInfo": {
                "count": "@variableType(integer)",
                "currentPage": 1,
                "firstItem": 1,
                "hasMorePages": "@variableType(boolean)",
                "lastItem": "@variableType(integer)",
                "lastPage": 1,
                "perPage": 10,
                "total": "@variableType(integer)"
              }
            }
          }
        }
        """

