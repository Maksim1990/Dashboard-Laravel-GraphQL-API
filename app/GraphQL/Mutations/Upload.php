<?php


namespace App\GraphQL\Mutations;


use Illuminate\Support\Facades\Storage;

class Upload
{

    public function __invoke($root, array $args): ?array
    {
        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $args['file'];


        Storage::disk('local')->put($file->getClientOriginalName(), $file->get());
        return [
            '_id'=>"235252352",
            'name'=>$file->getBasename(),
            'extension'=>$file->getExtension(),
            'size'=>$file->getSize(),
        ];
    }
}
