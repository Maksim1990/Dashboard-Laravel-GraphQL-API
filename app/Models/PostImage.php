<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class PostImage extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'post_id','name','gdrive_basename','gdrive_path','url','mimetype','extension'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
