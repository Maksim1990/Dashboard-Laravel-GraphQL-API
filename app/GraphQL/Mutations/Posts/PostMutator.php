<?php

namespace App\GraphQL\Mutations\Posts;

use App\Models\Comment;
use App\Exceptions\GraphQL\GraphqlException;
use App\Jobs\CloudStorage\Files\DeleteImageFromPost;
use App\Models\Like;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\CategoryPosts;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class PostMutator
{
    public function create($rootValue, array $args): Post
    {
        $validator = Validator::make($args, [
            'type' => 'required',
            'short_description' => 'required',
            'title' => Rule::requiredIf($args['type'] === 'normal'),
            'description' => Rule::requiredIf($args['type'] === 'normal'),
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'post',
                    'category' => 'post_create',
                    'reason' => 'validation_error',
                ]
            );
        }
        $post = new Post();
        if (isset($args['unique_id']) && !empty($args['unique_id'])) {
            $post->_id = $args['unique_id'];
        }
        $post->title = $args['title'] ?? '';
        $post->description = $args['description'] ?? '';
        $post->short_description = $args['short_description'] ?? '';
        $post->user_id = Auth::id();
        $post->type = $args['type'] ?? 'normal';
        $post->save();

        //-- Put post in categories if Category IDs are provided
        if (isset($args['category_ids'])) {
            $this->setPostCategory($post->_id, $args['category_ids'], 'update');
        }
        Cache::tags(['posts'])->flush();
        return $post;
    }

    public function setPostCategory($post_id, $category_ids, string $action = 'create')
    {
        $categoriesIDs = CategoryPosts::where('post_id', $post_id)->pluck('category_id')->toArray();
        if (!empty($category_ids)) {
            $arrCategoryIDs = explode(',', $category_ids);
            if (!empty($arrCategoryIDs)) {
                foreach ($arrCategoryIDs as $category_id) {
                    if (empty($category_id)) {
                        throw new GraphqlException('Category ID can not be empty', [
                            'type' => 'post',
                            'category' => 'post_' . $action,
                            'reason' => 'Invalid category ID',
                        ]);
                    }

                    if (Category::where('_id', $category_id)->first() === null) {
                        throw new GraphqlException('Category not found', [
                            'type' => 'post',
                            'category' => 'post_' . $action,
                            'reason' => 'Not found',
                        ]);
                    }
                    $rules = [
                        'post_id' => 'unique:category_posts,post_id,NULL,id,category_id,' . $category_id,
                        'category_id' => 'unique:category_posts,category_id,NULL,id,post_id,' . $post_id,
                    ];
                    $validator = Validator::make([
                        'post_id' => $post_id,
                        'category_id' => $category_id,
                    ], $rules);
                    if (!$validator->fails()) {
                        $categoryPosts = new CategoryPosts();
                        $categoryPosts->post_id = $post_id;
                        $categoryPosts->category_id = $category_id;
                        $categoryPosts->save();
                    }
                    if (in_array($category_id, $categoriesIDs)) {
                        if (($key = array_search($category_id, $categoriesIDs)) !== false) {
                            unset($categoriesIDs[$key]);
                        }
                    }
                }
            }
        }
        //-- If some categories left than remove post from this category
        if (!empty($categoriesIDs)) {
            foreach ($categoriesIDs as $item_id) {
                CategoryPosts::where('post_id', $post_id)->where('category_id', $item_id)->delete();
            }
        }
    }

    public function update($rootValue, array $args): Post
    {

        $validator = Validator::make($args, [
            '_id' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'post',
                    'category' => 'post_create',
                    'reason' => 'validation_error',
                ]
            );
        }

        if (($post = Post::find($args['_id'])->load('images')) === null) {
            throw new GraphqlException('Post not found', [
                'type' => 'post',
                'category' => 'post_update',
                'reason' => 'Not found',
            ]);
        }
        if (isset($args['title'])) {
            $post->title = $args['title'];
        }

        if (isset($args['description'])) {
            $post->description = $args['description'];
        }
        if (isset($args['short_description'])) {
            $post->short_description = $args['short_description'];
        }
        if (isset($args['type'])) {
            $post->type = $args['type'];
        }

        $post->update();
        Cache::tags(['posts', sprintf('post_%s', $post->_id)])->flush();

        //-- Put post in categories if Category IDs are provided
        if (isset($args['category_ids'])) {
            $this->setPostCategory($post->_id, $args['category_ids'], 'update');
        }

        //-- Check if need to remove some images that was removed while updating post
        $this->checkIfNeedToRemoveImagesFromPost($post);

        return $post;
    }

    public function checkIfNeedToRemoveImagesFromPost($post)
    {
        foreach ($post->images as $image) {
            if (!str_contains($post->description, $image->gdrive_basename)) {
                DeleteImageFromPost::dispatch($post, $image);
            }
        }
    }

    public function delete($rootValue, array $args): Post
    {
        $validator = Validator::make($args, ['_id' => 'required']);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'post',
                    'category' => 'post_delete',
                    'reason' => 'validation_error',
                ]
            );
        }

        if (($post = Post::find($args['_id'])->load('images')) === null) {
            throw new GraphqlException('Post not found', [
                'type' => 'post',
                'category' => 'post_delete',
                'reason' => 'Not found',
            ]);
        }

        //-- Delete linked Likes, bookmarks, comments & images
        Like::where('post_id', $post->_id)->delete();
        Comment::where('post_id', $post->_id)->delete();
        Bookmark::where('post_id', $post->_id)->delete();
        CategoryPosts::where('post_id', $post->_id)->delete();

        if (count($post->images) > 0) {
            foreach ($post->images as $image) {
                DeleteImageFromPost::dispatch($post, $image);
            }
        }
        $postToReturn = $post;
        Cache::tags(['posts', sprintf('post_%s', $post->_id)])->flush();
        $post->delete();
        return $postToReturn;

    }
}
