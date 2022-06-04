<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class File extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'name','type','size','extension'
    ];
}
