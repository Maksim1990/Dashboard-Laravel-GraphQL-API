<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class UserImage extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'user_id','name','type','gdrive_basename','gdrive_path','url','mimetype','extension'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
