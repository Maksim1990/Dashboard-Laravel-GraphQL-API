<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class UserCloudFolders extends Eloquent
{
    use HybridRelations;

    protected $connection = 'mongodb';
    protected $primaryKey = '_id';
    protected $collection = 'user_cloud_folders';

    protected $fillable = [
        'user_id',
        'main',
        'images',
        'posts',
        'documents',
        'messages',
        'storage'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
