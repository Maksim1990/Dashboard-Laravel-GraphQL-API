<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class CategoryPosts extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'category_id','post_id'
    ];
}
