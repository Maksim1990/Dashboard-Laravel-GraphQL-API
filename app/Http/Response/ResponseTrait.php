<?php
namespace App\Http\Response;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    protected function getErrorResponse($data): JsonResponse
    {
        $errorInfo = [
            'code' => $data->getCode(),
            'message' => $data->getMessage(),
        ];

        return new JsonResponse($errorInfo, $data->getCode());
    }
}
