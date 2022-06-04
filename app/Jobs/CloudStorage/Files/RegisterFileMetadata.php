<?php

namespace App\Jobs\CloudStorage\Files;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RegisterFileMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /** @var \Illuminate\Http\UploadedFile $file */
    private $filename;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $item_id;

    /**
     * @var string
     */
    private $item_column;


    private $model;

    /** @var array $params */
    private $params;

    public function __construct(array $arrOptions)
    {
        $this->file=$arrOptions['file']??null;
        $this->model=$arrOptions['model']??null;
        $this->filename=$arrOptions['filename']??'sample.png';
        $this->item_id=$arrOptions['item_id']??null;
        if(!is_null($this->item_id)){
            $this->item_column=$arrOptions['item_column']??"post_id";
        }

        $this->params=$arrOptions['params']??null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!is_null($this->file)){
            $file=new $this->model();
            $file->name = trim($this->filename);

            if(!is_null($this->params)) {
                foreach ($this->params as $column=>$val){
                    $file->{$column} = trim($val);
                }
            }

            if(!is_null($this->item_id)) {
                $file->{$this->item_column} = trim($this->item_id);
            }

            $file->gdrive_basename = trim($this->file['basename']);
            $file->gdrive_path = trim($this->file['path']);
            switch (config('filesystems.cloud')) {
                case 'google':
                    $file->url = Storage::cloud()->url(trim($this->file['path']));
                    break;
                default:
                    $file->url = Storage::url(trim($this->file['path']));
            }

            $file->mimetype = trim($this->file['mimetype']);
            $file->extension = trim($this->file['extension']);
            $file->save();
        }

    }
}
