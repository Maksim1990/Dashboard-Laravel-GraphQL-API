<?php
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return sprintf('Welcome to %s',config('app.name'));
})->name('main');

Route::post('/blog/images/resize', 'BlogController@imagesResize')->name('blog_images_resize');
Route::post('/user/images/upload', 'UserController@uploadImage')->name('user_upload_image');

####### K8s probe endpoints
Route::get('/healthz', function () {
    return 'ok';
});

Route::get('/readiness', function () {
    $check = DB::table('migrations')->count();
    if ($check > 0) {
        return 'ok';
    }
    return response('', 500);
});
