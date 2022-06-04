<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Spatie\QueryBuilder\QueryBuilder;

class Message extends Eloquent
{
    use HybridRelations;
    use HasFactory;

    protected $fillable = [
        'text',
        'user_id',
        'from_user_id',
        'file_id',
        'is_read'
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, "from_user_id", "_id");
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id", "_id");
    }

    public function getDocumentId()
    {
        return $this->getAttribute("_id");
    }

    public function getMessage($root, array $args): Model
    {
        $cacheKey = sprintf('message_%s', $args['_id']);
        return Cache::tags($cacheKey)
            ->remember(
                $cacheKey,
                now()->addMonth(),
                function () use ($args) {
                    return QueryBuilder::for(Message::class)->where('_id', $args['_id'])->first();
                }
            );
    }
}
