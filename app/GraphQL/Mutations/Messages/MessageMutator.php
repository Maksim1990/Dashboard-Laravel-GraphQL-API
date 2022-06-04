<?php

namespace App\GraphQL\Mutations\Messages;

use App\Models\Chat;
use App\Exceptions\GraphQL\GraphqlException;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MessageMutator
{
    public function create($rootValue, array $args): array
    {
        $validator = Validator::make($args, [
            'text' => 'required',
            'user_id' => 'required',
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
        $message = new Message();
        $message->text = $args['text'];
        $message->from_user_id = Auth::id();
        $message->user_id = $args['user_id'];
        $message->file_id = $args['file_id'] ?? 0;
        $message->is_read = false;
        $message->type = 'text';
        $message->save();

        $chat = Chat::where(function ($q) use ($args) {
            $q->where('user_id', $args['user_id'])->where('from_user_id', Auth::id());
        })
            ->orWhere(function ($q) use ($args) {
                $q->where('user_id', Auth::id())->where('from_user_id', $args['user_id']);
            })->first();

        if (is_null($chat)) {
            $chat = new Chat();
            $chat->from_user_id = Auth::id();
            $chat->user_id = $args['user_id'];
            $chat->has_messages = true;
            $chat->last_message_date = Carbon::createFromDate($message->created_at->toDateTimeString())->format('Y-m-d H:i:s');
            $chat->number_unread_messages = 1;
            $chat->number_messages = 1;
            $chat->save();
        } else {
            $messages = $this->getChatMessages($chat);
            $chat->number_unread_messages = $messages->filter(function ($message) {
                return !$message->is_read;
            })->count();
            $chat->number_messages = $messages->count();
            $chat->last_message_date = Carbon::createFromDate($message->created_at->toDateTimeString())->format('Y-m-d H:i:s');
            $chat->update();
        }

//            //-- Broadcast new message
//            broadcast(new NewMessage($chat->_id, $message))->toOthers();

        Cache::tags(['messages'])->flush();

        return [
            'message' => $message,
            'chat' => $chat,
        ];
    }

    private function getChatMessages($chat)
    {
        return Message::where(function ($q) use ($chat) {
            $q->where('user_id', $chat->firstMember->_id)->where('from_user_id', $chat->secondMember->_id);
        })
            ->orWhere(function ($q) use ($chat) {
                $q->where('user_id', $chat->secondMember->_id)->where('from_user_id', $chat->firstMember->_id);
            })->get();
    }

    public function update($rootValue, array $args): Message
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

        if (($message = Message::find($args['_id'])) === null) {
            throw new GraphqlException('Message not found', [
                'type' => 'message',
                'category' => 'message_update',
                'reason' => 'Not found',
            ]);
        }
        if (isset($args['text'])) {
            $message->text = $args['text'];
        }

        $message->update();
        Cache::tags(['messages', sprintf('message_%s', $message->_id)])->flush();
        return $message;

    }

    public function delete($rootValue, array $args): Collection
    {
        $validator = Validator::make($args, [
            '_id' => 'required',
        ]);
        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'message',
                    'category' => 'message_delete',
                    'reason' => 'validation_error',
                ]
            );
        }

        if (empty($messages = Message::whereIn('_id', $args['_id'])->get())) {
            throw new GraphqlException('Message not found', [
                'type' => 'message',
                'category' => 'message_delete',
                'reason' => 'Not found',
            ]);
        }

        array_walk(
            $messages,
            function (Model $message) {
                $chat = Chat::where(function ($q) use ($message) {
                    $q->where('user_id', $message->sender->_id)->where('from_user_id', $message->receiver->_id);
                })
                    ->orWhere(function ($q) use ($message) {
                        $q->where('user_id', $message->receiver->_id)->where('from_user_id', $message->sender->_id);
                    })->first();
                Cache::tags(sprintf('message_%s', $message->_id))->flush();
                $message->delete();
                if ($chat->number_messages <= 1) {
                    $chat->delete();
                } else {
                    $chat->number_messages -= 1;
                    $chat->number_unread_messages = $this->getChatMessages($chat)->filter(function ($message) {
                        return !$message->is_read;
                    })->count();
                    $chat->update();
                }
            }
        );

        Cache::tags(['messages'])->flush();
        return $messages;
    }
}
