<?php

namespace App\GraphQL\Queries;

use App\Exceptions\GraphQL\GraphqlException;
use Illuminate\Support\Facades\Storage;

class GetImage
{
    public function __invoke($rootValue, array $args): array
    {
        $imageName = $args['imageName'];
        $filePath = sprintf('images/%s.png', $imageName);
        if (!Storage::disk('s3')->has($filePath)) {
            throw new GraphqlException(sprintf(
                'Image %s was not found', $imageName),
                [
                    'type' => 'image',
                    'category' => 'get_image',
                    'reason' => 'Not found',
                ]
            );
        }

        return ['content' => Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(5))];
    }
}
