<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function imagesResize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'image' => 'required',
        ]);
        if (!$validator->fails()) {

            if (!$request->has('post_id')) {
                //-- Generate unique new post ID
                do {
                    $post_id = md5(uniqid(rand(), true));
                } while (
                    Post::where('_id', '=', $post_id)->exists()
                );
            } else {
                $post_id = $request->post_id;
            }
            $user_id = $request->user_id;

            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $request->image;
            $filename = date("YmdHis") . "_" . $file->getClientOriginalName();

            $user = User::with('folders')->where('_id', $user_id)->first();

//            RegisterFileMetadata::dispatch([
//                'file' => $uploadedFile,
//                'model' => PostImage::class,
//                'filename' => $filename,
//                'item_id' => $post_id,
//                'item_column' => 'post_id',
//            ]);

            return response()
                ->json([
                    'hasError' => false,
                    'post_id' => $post_id,
                    'name' => $file->getBasename(),
                    'extension' => $file->getExtension(),
                    'size' => $file->getSize(),
//                    'url' => config('filesystems.cloud') === 'google' ?
//                        Storage::cloud()->url(trim($uploadedFile['path'])) : $uploadedFile['presigned_url'],
                ]);
        } else {
            return response()
                ->json([
                    'hasError' => true,
                    'errorMessage' => $validator->messages()->first(),
                ]);
        }
    }
}
