<?php

namespace App\GraphQL\Mutations\Comments;

use App\Models\Comment;
use App\Exceptions\GraphQL\GraphqlException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentMutator
{
    public function create($rootValue, array $args): Comment
    {
        $validator = Validator::make($args, [
            'text' => 'required',
            'post_id' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'comment',
                    'category' => 'comment_create',
                    'reason' => 'validation_error',
                ]
            );
        }

        $comment = new Comment();
        $comment->text = $args['text'];
        $comment->post_id = $args['post_id'];
        $comment->user_id = Auth::id();
        $comment->save();

        return $comment;
    }

    public function update($rootValue, array $args): Comment
    {
        $validator = Validator::make($args, [
            '_id' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'message',
                    'category' => 'message_create',
                    'reason' => 'validation_error',
                ]
            );
        }
        $comment = Comment::find($args['_id']);
        if (is_null($comment)) {
            throw new GraphqlException('Comment not found', [
                'type' => 'comment',
                'category' => 'comment_update',
                'reason' => 'Not found',
            ]);
        }
        if (isset($args['text'])) {
            $comment->text = $args['text'];
        }

        $comment->update();
        return $comment;
    }

    public function delete($rootValue, array $args): Comment
    {
        $validator = Validator::make($args, [
            '_id' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'comment',
                    'category' => 'comment_delete',
                    'reason' => 'validation_error',
                ]
            );
        }
        $comment = Comment::find($args['_id']);
        if (is_null($comment)) {
            throw new GraphqlException('Comment not found', [
                'type' => 'comment',
                'category' => 'comment_delete',
                'reason' => 'Not found',
            ]);
        }
        $comment->delete();
        return $comment;
    }
}
