<?php

namespace App\GraphQL\Queries;

use App\Exceptions\GraphQL\GraphqlException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GetMarkdownPageContent
{
    public function __invoke($rootValue, array $args): array
    {
        $validator = Validator::make($args, [
            'type' => 'required|in:about,contact-us,releases-fe,releases-be,dev-stack',
        ]);

        if ($validator->fails()) {
            throw new GraphqlException(
                implode(',', $validator->messages()->all()),
                [
                    'type' => 'markdown_content',
                    'category' => 'markdown',
                    'reason' => 'markdown_type_validation_error',
                ]
            );
        }

        $fileContent = Cache::tags(['markdown'])
            ->remember(
                $args['type'],
                now()->addMonth(),
                fn() => $this->getMarkdownContentFile($args['type'])
            );

        return [
            'content' => $fileContent,
            'type' => $args['type'],
            'status' => !empty($fileContent),
            'message' => !empty($fileContent) ? 'Successfully received content' : 'Failure while receiving file content',
        ];
    }

    private function getMarkdownContentFile(string $filename): ?string
    {
        $filePath = sprintf('markdown-content/%s.md', $filename);
        if (!Storage::disk('s3')->has($filePath)) {
            return null;
        }

        return Storage::disk('s3')->get($filePath);
    }
}
