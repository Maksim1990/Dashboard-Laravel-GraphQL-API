<?php

namespace App\Http\Controllers;


use App\Jobs\CloudStorage\Files\RegisterFileMetadata;
use App\Jobs\CloudStorage\User\DeleteImageFromUser;
use App\Models\UserImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
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

    public function uploadImage(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required',
            'type' => 'required',
        ]);
        if (!$validator->fails()) {
            $type = $request->type;
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $request->file;
            $filename = "avatar." . $file->clientExtension();

            $user = User::with('folders')->where('_id', Auth::id())->first();
            if ($type === 'avatar') {
                $image = UserImage::where('user_id', Auth::id())->where('type', $type)->first();
                if (!is_null($image)) {
                    DeleteImageFromUser::dispatch($user, $image);
                }
//                RegisterFileMetadata::dispatch([
//                    'file'=>$uploadedFile,
//                    'model'=>UserImage::class,
//                    'filename'=>$filename,
//                    'item_id'=>Auth::id(),
//                    'item_column'=>'user_id',
//                    'params'=>[
//                        'type'=>'avatar'
//                    ]
//                ]);
            }


            return response()
                ->json([
                    'hasError' => false,
                    'folder' => $user->folders->storage,
                    'file' => $filename,
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
