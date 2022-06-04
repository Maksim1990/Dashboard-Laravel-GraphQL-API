<?php

namespace App\Jobs\CloudStorage\User;



use App\Models\UserImage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteImageFromUser implements ShouldQueue
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
     * @param UserImage $image
     */
    public function __construct(User $user, UserImage $image)
    {
        $this->user = $user;
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        UserImage::where("_id", $this->image->_id)->delete();
        switch (config('filesystems.cloud')) {
            case 'google':
                Storage::disk('google')->delete($this->image->gdrive_path);
                break;
            default:
                Storage::delete($this->image->gdrive_path);
        }

    }
}
