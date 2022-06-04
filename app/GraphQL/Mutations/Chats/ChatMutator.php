<?php

namespace App\GraphQL\Mutations\Chats;

use App\Models\Chat;
use App\Exceptions\GraphQL\GraphqlException;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ChatMutator
{
    public function delete($rootValue, array $args): Chat
    {
        $validator = Validator::make($args, [
            '_id' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'chat',
                    'category' => 'chat_delete',
                    'reason' => 'validation_error',
                ]
            );
        }

        $chat = Chat::find($args['_id']);
        if (is_null($chat)) {
            throw new GraphqlException('Chat not found', [
                'type' => 'chat',
                'category' => 'chat_delete',
                'reason' => 'Not found',
            ]);
        }

        Message::where(function ($q) use ($chat) {
            $q->where('from_user_id', $chat->firstMember->_id)->where('user_id', $chat->secondMember->_id);
        })
            ->orWhere(function ($q) use ($chat) {
                $q->where('from_user_id', $chat->secondMember->_id)->where('user_id', $chat->firstMember->_id);
            })->delete();

        Cache::tags(['chats',sprintf('chat_%s', $chat->_id)])->flush();
        $chat->delete();
        return $chat;
    }
}
