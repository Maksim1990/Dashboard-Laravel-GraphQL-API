<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

trait ResolveSearchableIndex
{
    #[Pure] public function getSearchableIndexSuffix(string $env): string
    {
        return match (Str::lower($env)) {
            'production' => 'prod',
            default => 'dev',
        };
    }
}
