<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class Settings extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'user_id','locale'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
