<?php

namespace App\Jobs\User;

use App\Models\Message;
use App\Models\PostImage;
use App\Models\Settings;
use App\Models\UserCloudFolders;
use App\Models\UserImage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;
    /**
     * @var UserImage
     */
    private $image;

    /**
     * DeleteImageFromPost constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->user->loadMissing('folders');
    }


    public function handle()
    {
        //-- Delete linked posts and via loop from Elasticsearch index
        $posts = Post::where('user_id', $this->user->_id)->get();
        if (!is_null($posts)) {
            foreach ($posts as $post) {
                PostImage::where('post_id', $post->_id)->delete();
                $post->delete();
            }
        }

        //-- Delete linked messages and via loop from Elasticsearch index
        $messages = Message::where('user_id', $this->user->_id)->get();
        if (!is_null($messages)) {
            foreach ($messages as $message) {
                $message->delete();
            }
        }

        Settings::where('user_id', $this->user->_id)->delete();
        UserImage::where('user_id', $this->user->_id)->delete();
        Storage::disk('google')->delete($this->user->folders->main);
        UserCloudFolders::where('user_id', $this->user->_id)->delete();

        $this->user->delete();
    }
}
