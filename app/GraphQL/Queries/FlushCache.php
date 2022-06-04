<?php

namespace App\GraphQL\Queries;

use Illuminate\Support\Facades\Cache;

class FlushCache
{
    public function __invoke($rootValue, array $args): array
    {
        $type = $args['type'] ?? null;
        if ($type === null) {
            Cache::flush();
            return $this->getFlushCacheResponse();
        }

        Cache::tags(explode(',', $type))->flush();
        return $this->getFlushCacheResponse(
            sprintf('Cache tages \'%s\' was successfully flushed', $type)
        );
    }

    private function getFlushCacheResponse(string $message = 'Cache was successfully flushed'): array
    {
        return ['status' => true, 'message' => $message];
    }
}
