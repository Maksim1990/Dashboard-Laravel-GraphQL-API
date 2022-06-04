<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'text',
        'user_id',
        'post_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function getDocumentId()
    {
        return $this->getAttribute("_id");
    }
}
