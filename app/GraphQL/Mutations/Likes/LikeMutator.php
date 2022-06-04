<?php

namespace App\GraphQL\Mutations\Likes;

use App\Exceptions\GraphQL\GraphqlException;
use App\GraphQL\Traits\PostTrait;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LikeMutator
{
    use PostTrait;

    public function like($rootValue, array $args): Like
    {
        $validator = Validator::make($args, ['post_id' => 'required',]);

        $like = Like::where('post_id', $args['post_id'])->where('user_id', Auth::id())->first();
        $this->checkIfPostExist($args['post_id']);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'like',
                    'category' => 'like',
                    'reason' => 'validation_error',
                ]
            );
        }

        if ($like !== null) {
            throw new GraphqlException(
                'Like already exist',
                [
                    'type' => 'like',
                    'category' => 'like_exist',
                    'reason' => 'Like already exist',
                ]
            );
        }

        $like = new Like();
        $like->post_id = $args['post_id'];
        $like->user_id = Auth::id();
        $like->save();
        return $like;
    }

    public function unlike($rootValue, array $args): Like
    {
        $validator = Validator::make($args, ['post_id' => 'required']);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'like',
                    'category' => 'unlike',
                    'reason' => 'validation_error',
                ]
            );
        }

        $this->checkIfPostExist($args['post_id']);
        $like = Like::where('post_id', $args['post_id'])->first();

        if ($like === null) {
            throw new GraphqlException(
                sprintf('Like with ID %s not found', $args['post_id']),
                [
                    'type' => 'like',
                    'category' => 'like_not_exist',
                    'reason' => 'Like does not exist',
                ]
            );
        }
        $like->delete();
        return $like;

    }
}
