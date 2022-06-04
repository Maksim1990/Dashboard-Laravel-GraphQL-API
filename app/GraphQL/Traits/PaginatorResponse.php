<?php

namespace App\GraphQL\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait PaginatorResponse
{
    public function getAbstractPaginatorResponse(
        LengthAwarePaginator $response,
        int                  $count = 0,
        bool                 $useCache = false
    ): array
    {
        return [
            'data' => $response->items(),
            'isFromCache' => $useCache,
            'paginatorInfo' => [
                'currentPage' => $response->currentPage(),
                'firstItem' => $response->firstItem(),
                'hasMorePages' => $response->hasMorePages(),
                'lastItem' => $response->lastItem(),
                'lastPage' => $response->lastPage(),
                'perPage' => $response->perPage(),
                'total' => $response->total(),
                'count' => $count,
            ]
        ];
    }
}
