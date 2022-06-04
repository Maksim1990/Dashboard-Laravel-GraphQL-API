<?php

namespace App\GraphQL\Traits;

use App\Exceptions\GraphQL\GraphqlException;
use App\Models\Post;

trait PostTrait
{
    private function checkIfPostExist(string $postId)
    {
        if (Post::where('_id', $postId)->first() === null) {
            throw new GraphqlException(
                'Post was not found',
                [
                    'type' => 'like',
                    'category' => 'post_not_found',
                    'reason' => 'Post was not found',
                ]
            );
        }
    }

}
