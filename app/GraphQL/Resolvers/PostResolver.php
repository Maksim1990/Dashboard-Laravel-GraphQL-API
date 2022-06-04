<?php

namespace App\GraphQL\Resolvers;

use App\Models\Like;
use App\Models\Bookmark;
use Illuminate\Support\Facades\Auth;

class PostResolver
{
    function isLikedByAuthUser($rootValue)
    {
        return Like::where('post_id', $rootValue['_id'])->where('user_id', $rootValue['user_id_open_post'] ?? null)->exists();
    }

    function isBookmarkedByAuthUser($rootValue)
    {
        return Bookmark::where('post_id', $rootValue['_id'])->where('user_id', Auth::id())->exists();
    }
}
