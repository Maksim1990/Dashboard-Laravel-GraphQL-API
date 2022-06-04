<?php

namespace App\GraphQL\Mutations\Bookmarks;


use App\Exceptions\GraphQL\GraphqlException;
use App\Models\Bookmark;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Support\Facades\Validator;

class BookmarkMutator
{

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return Bookmark
     * @throws GraphqlException
     */
    public function addBookmark($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Bookmark
    {
        $rules = [
            'user_id' => 'unique:bookmarks,user_id,NULL,id,post_id,' . $args['post_id'],
            'post_id' => 'unique:bookmarks,post_id,NULL,id,user_id,' . $args['user_id'],
        ];

        $validator = Validator::make($args, $rules);

        if (!$validator->fails()) {
            $bookmark = new Bookmark();
            $bookmark->user_id = $args['user_id'];
            $bookmark->post_id = $args['post_id'];
            $bookmark->save();
            return $bookmark;

        } else {
            throw new GraphqlException(
                $validator->messages()->all(),
                [
                    'type' => 'bookmark',
                    'category' => 'bookmark',
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
     * @return Bookmark
     * @throws GraphqlException
     */
    public function removeBookmark($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Bookmark
    {
        $rules = [
            'user_id' => 'required',
            'post_id' => 'required',
        ];

        $validator = Validator::make($args, $rules);

        $bookmark=Bookmark::where('post_id',$args['post_id'])->where('user_id',$args['user_id'])->first();
        if(!is_null($bookmark)){
            if (!$validator->fails()) {
                $bookmark->delete();
                return $bookmark;
            } else {
                throw new GraphqlException(
                    $validator->messages()->all(),
                    [
                        'type' => 'bookmark',
                        'category' => 'bookmark',
                        'reason' => 'validation_error',
                    ]
                );
            }
        }else{
            throw new GraphqlException(
                'Bookmark was not found',
                [
                    'type' => 'bookmark',
                    'category' => 'bookmark',
                    'reason' => 'Bookmark was not found',
                ]
            );
        }
    }
}
