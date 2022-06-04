<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Like extends Eloquent
{
    use HybridRelations;

    protected $fillable = ['text','user_id','post_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class,'post_id','_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','_id');
    }


}
