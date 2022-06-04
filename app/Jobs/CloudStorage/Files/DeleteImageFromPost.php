<?php

namespace App\Jobs\CloudStorage\Files;

use App\Models\PostImage;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteImageFromPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Post
     */
    private $post;
    /**
     * @var PostImage
     */
    private $image;

    /**
     * DeleteImageFromPost constructor.
     * @param Post $post
     * @param PostImage $image
     */
    public function __construct(Post $post, PostImage $image)
    {
        $this->post = $post;
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        PostImage::where("_id", $this->image->_id)->delete();
        Storage::delete($this->image->gdrive_path);
        $this->post->delete();
    }
}
