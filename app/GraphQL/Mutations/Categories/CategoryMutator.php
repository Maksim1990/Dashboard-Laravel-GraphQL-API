<?php

namespace App\GraphQL\Mutations\Categories;


use App\Exceptions\GraphQL\GraphqlException;
use App\Models\Category;
use App\Models\CategoryPosts;
use App\Models\Post;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Support\Facades\Validator;

class CategoryMutator
{

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return Category
     * @throws GraphqlException
     */
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Category
    {
        $validator = Validator::make($args, [
            'title' => 'required|unique:categories',
        ]);
        if (!$validator->fails()) {
            $category = new Category();
            $category->title = $args['title'];
            $category->description = $args['description'] ?? "";
            $category->created_by = Auth::id();
            $category->save();

            return $category;
        } else {
            throw new GraphqlException(
                $validator->messages()->all(),
                [
                    'type' => 'category',
                    'category' => 'category_create',
                    'reason' => 'validation_error',
                ]
            );
        }
    }

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return Category
     * @throws GraphqlException
     */
    public function update($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Category
    {

        $validator = Validator::make($args, [
            '_id' => 'required',
            'title' => 'required|unique:categories,title,' . $args['_id'] . ",_id"
        ]);
        if (!$validator->fails()) {
            $category = Category::find($args['_id']);
            if (!is_null($category)) {
                if (isset($args['title'])) {
                    $category->title = $args['title'];
                }

                if (isset($args['description'])) {
                    $category->description = $args['description'];
                }

                $category->update();
                return $category;
            } else {
                throw new GraphqlException('Category not found', [
                    'type' => 'category',
                    'category' => 'category_update',
                    'reason' => 'Not found',
                ]);
            }
        } else {
            throw new GraphqlException(
                $validator->messages()->all(),
                [
                    'type' => 'category',
                    'category' => 'category_create',
                    'reason' => 'validation_error',
                ]
            );
        }
    }


    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return Category
     * @throws GraphqlException
     */
    public function delete($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Category
    {
        $validator = Validator::make($args, [
            '_id' => 'required',
        ]);
        if (!$validator->fails()) {
            $comment = Category::find($args['_id']);
            if (!is_null($comment)) {
                $comment->delete();
                return $comment;
            } else {
                throw new GraphqlException('Category not found', [
                    'type' => 'category',
                    'category' => 'category_delete',
                    'reason' => 'Not found',
                ]);
            }
        } else {
            throw new GraphqlException(
                $validator->messages()->all(),
                [
                    'type' => 'category',
                    'category' => 'category_delete',
                    'reason' => 'validation_error',
                ]
            );
        }
    }


    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return Category
     * @throws GraphqlException
     */
    public function addPostToCategory($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Category
    {
        $rules = [
            'post_id' => 'unique:category_posts,post_id,NULL,id,category_id,' . $args['category_id'],
            'category_id' => 'unique:category_posts,category_id,NULL,id,post_id,' . $args['post_id'],
        ];

        $validator = Validator::make($args, $rules);


        if (!$validator->fails()) {
            $category = Category::find($args['category_id']);
            if (is_null($category)) {
                throw new GraphqlException('Category not found', [
                    'type' => 'category',
                    'category' => 'category_add_post',
                    'reason' => 'Not found',
                ]);
            }
            $post = Post::find($args['post_id']);
            if (is_null($post)) {
                throw new GraphqlException('Post not found', [
                    'type' => 'category',
                    'category' => 'category_add_post',
                    'reason' => 'Not found',
                ]);
            }

            $categoryPosts = new CategoryPosts();
            $categoryPosts->post_id = $args['post_id'];
            $categoryPosts->category_id = $args['category_id'];
            $categoryPosts->save();

            return $category;
        } else {
            throw new GraphqlException(
                $validator->messages()->all(),
                [
                    'type' => 'category',
                    'category' => 'category_add_post',
                    'reason' => 'validation_error',
                ]
            );
        }
    }

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return Post
     * @throws GraphqlException
     */
    public function removePostFromCategory($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Post
    {
        $rules = [
            'post_id' => 'unique:bookmarks,user_id,NULL,id,category_id,' . $args['category_id'],
            'category_id' => 'unique:bookmarks,post_id,NULL,id,post_id,' . $args['post_id'],
        ];

        $validator = Validator::make($args, $rules);


        if (!$validator->fails()) {
            $category = Category::find($args['category_id']);
            if (is_null($category)) {
                throw new GraphqlException('Category not found', [
                    'type' => 'category',
                    'category' => 'category_delete_post',
                    'reason' => 'Not found',
                ]);
            }
            $post = Post::find($args['post_id']);
            if (is_null($post)) {
                throw new GraphqlException('Post not found', [
                    'type' => 'category',
                    'category' => 'category_delete_post',
                    'reason' => 'Not found',
                ]);
            }

            $categoryPosts=CategoryPosts::where('post_id',$args['post_id'])->where('category_id',$args['category_id'])->first();
            if(!is_null($categoryPosts)){
                $categoryPosts->delete();
                    return $post;

            }else{
                throw new GraphqlException(
                    'Post was not in category',
                    [
                        'type' => 'category',
                        'category' => 'category_delete_post',
                        'reason' => 'Post was not in category',
                    ]
                );
            }
        } else {
            throw new GraphqlException(
                $validator->messages()->all(),
                [
                    'type' => 'category',
                    'category' => 'category_delete_post',
                    'reason' => 'validation_error',
                ]
            );
        }
    }
}
