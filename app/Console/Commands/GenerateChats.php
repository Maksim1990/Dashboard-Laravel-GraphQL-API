<?php

namespace App\Console\Commands;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize chats based on existing messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $messages = Message::all();
        foreach ($messages as $message) {
       try{
           $chat = Chat::where(function ($q) use ($message) {
               $q->where('user_id', $message->sender->_id)->where('from_user_id', $message->receiver->_id);
           })
               ->orWhere(function ($q) use ($message) {
                   $q->where('user_id', $message->receiver->_id)->where('from_user_id', $message->sender->_id);
               })->first();

           if (is_null($chat)) {
               $chat = new Chat();
               $chat->from_user_id = $message->sender->_id;
               $chat->user_id = $message->receiver->_id;
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
       }catch (\Throwable $e){
           dd($message->receiver);
       }

        }
    }

    /**
     * @param $chat
     * @return mixed
     */
    private function getChatMessages($chat)
    {
        return Message::where(function ($q) use ($chat) {
            $q->where('user_id', $chat->firstMember->_id)->where('from_user_id', $chat->secondMember->_id);
        })
            ->orWhere(function ($q) use ($chat) {
                $q->where('user_id', $chat->secondMember->_id)->where('from_user_id', $chat->firstMember->_id);
            })->get();
    }
}
