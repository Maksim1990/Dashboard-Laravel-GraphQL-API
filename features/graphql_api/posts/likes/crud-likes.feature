@api-posts @crud-likes
Feature: Manage likes
    In order to use application
    As an application user
    I need to be able to like and unlike posts

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
                  user{
                      _id
                    }
              }
          }
        """
        When I request "/graphql" using HTTP POST
        Then I get user ID and auth token from the response
        And I save it into "authToken,userId"
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

       # Like post with invalid post ID
        Then the graphql request body is:
        """
        mutation{
          like(post_id:"INVALID_ID"){
            _id
            user{
              _id
            }
            post{
              _id
            }
          }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "errors": [
                {
                  "messages": [
                    "Post was not found"
                  ],
                  "code": 400
               }
          ]
        }
        """

        # Like new post
        Then the graphql request body is:
        """
        mutation{
          like(post_id:"<<postId>>"){
            _id
            user{
              _id
            }
            post{
              _id
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
            "like": {
              "_id": "@variableType(string)",
              "user": {
                "_id": "<<userId>>"
              },
              "post": {
                "_id": "<<postId>>"
              }
            }
          }
        }
        """

        # Get post likes
        Then the graphql request body is:
        """
        query{
          likes(post_id:"<<postId>>")
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "likes": 1
          }
        }
        """

        # Like again the same new post
        Then the graphql request body is:
        """
        mutation{
          like(post_id:"<<postId>>"){
            _id
            user{
              _id
            }
            post{
              _id
            }
          }
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "errors": [
                {
                  "messages": [
                    "Like already exist"
                  ],
                  "code": 400
               }
          ]
        }
        """

        # Unlike new post
        Then the graphql request body is:
        """
        mutation{
          unlike(post_id:"<<postId>>"){
            _id
            user{
              _id
            }
            post{
              _id
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
            "unlike": {
              "_id": "@variableType(string)",
              "user": {
                "_id": "<<userId>>"
              },
              "post": {
                "_id": "<<postId>>"
              }
            }
          }
        }
        """

        # Get post likes
        Then the graphql request body is:
        """
        query{
          likes(post_id:"<<postId>>")
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "data": {
            "likes": 0
          }
        }
        """

        # Get post likes with invalid post ID
        Then the graphql request body is:
        """
        query{
          likes(post_id:"INVALID_ID")
        }
        """
        When I request "/graphql" using HTTP POST
        And the response code is 200
        And the response body contains JSON:
        """
        {
          "errors": [
                {
                  "messages": [
                    "Post was not found"
                  ],
                  "code": 400
               }
          ]
        }
        """

