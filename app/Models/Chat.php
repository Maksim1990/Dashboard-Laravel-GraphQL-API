<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Spatie\QueryBuilder\QueryBuilder;

class Chat extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'user_id','from_user_id','has_messages','last_message_date','number_unread_messages','number_messages'
    ];


    public function firstMember(): BelongsTo
    {
        return $this->belongsTo(User::class,"from_user_id","_id");
    }

    public function secondMember(): BelongsTo
    {
        return $this->belongsTo(User::class,"user_id","_id");
    }

    public function getDocumentId(){
        return $this->getAttribute("_id");
    }

    public function getChat($root, array $args): Model
    {
        $cacheKey = sprintf('chat_%s', $args['_id']);
        return Cache::tags($cacheKey)
            ->remember(
                $cacheKey,
                now()->addMonth(),
                function () use ($args) {
                    return QueryBuilder::for(Chat::class)->where('_id', $args['_id'])->first();
                }
            );
    }
}
