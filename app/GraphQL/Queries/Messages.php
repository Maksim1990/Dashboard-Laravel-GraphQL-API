<?php

namespace App\GraphQL\Queries;

use App\Exceptions\GraphQL\GraphqlException;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class Messages
{
    public function __invoke($rootValue, array $args): Collection
    {
        $validator = Validator::make(
            $args,
            [
                'chat_id' => 'required',
                'user_id' => 'required',
            ]
        );

        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'Get messages: validation error',
                    'category' => 'get_messages',
                    'reason' => 'get_messages',
                ]
            );
        }
        $chat_id = $args['chat_id'];
        $user_id = $args['user_id'];
        $offset = $args['offset'] ?? 0;
        $number = $args['number'] ?? 10;

        if (($chat = Chat::find($chat_id)) === null) {
            return new Collection;
        }
        $orderBy = $args['orderBy'][0] ?? ['column' => 'created_at', 'order' => 'ASC',];
        $fromUserId = $chat->firstMember->_id !== $user_id ? $chat->firstMember->_id : $chat->secondMember->_id;

        return Cache::tags(['messages'])
            ->remember(
                sprintf(
                    'messages_%s_%s_%s_%s_%s_%s',
                    $chat_id,
                    $user_id,
                    $offset,
                    $number,
                    $orderBy['column'],
                    $orderBy['order']
                ),
                now()->addMonth(),
                function () use ($args, $fromUserId, $offset, $number, $user_id, $orderBy) {
                    return Message::where(function ($query) use ($user_id, $fromUserId) {
                        $query->where('user_id', $user_id)
                            ->where('from_user_id', $fromUserId);
                    })
                        ->orWhere(function ($query) use ($user_id, $fromUserId) {
                            $query->where('from_user_id', $user_id)
                                ->where('user_id', $fromUserId);
                        })
                        ->orderBy($orderBy['column'], $orderBy['order'])
                        ->skip($offset)->take($number)->get();
                }
            );
    }
}
