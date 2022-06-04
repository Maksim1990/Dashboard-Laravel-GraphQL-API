<?php

namespace App\Jobs\CloudStorage;

use App\Models\UserCloudFolders;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateCloudFolder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $options;

    /**
     * @var User $user
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param array $arrOptions
     */
    public function __construct(User $user, array $arrOptions = [])
    {
        $this->user = $user;
        $this->options = $arrOptions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $strPath = (isset($this->options['sub'])) ?
            $this->user->email . '/' . $this->options['folder_name'] :
            $this->user->email;

        Storage::makeDirectory($strPath);
        if (isset($this->options['sub'])) {
            UserCloudFolders::updateOrCreate(
                ['user_id' => $this->user->_id], [$this->options['folder_name'] => $strPath]
            );
        }
    }
}
