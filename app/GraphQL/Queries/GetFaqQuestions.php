<?php

namespace App\GraphQL\Queries;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GetFaqQuestions
{
    private const FAQ_CACHE_KEY = 'faq';

    public function __invoke($rootValue, array $args): array
    {
        return [
            'content' => json_decode(
                Cache::tags(['markdown'])
                ->remember(self::FAQ_CACHE_KEY, now()->addMonth(), fn() => $this->getFaqQuestions())
            ),
            'type' => self::FAQ_CACHE_KEY,
        ];
    }

    private function getFaqQuestions(): ?string
    {
        $filePath = sprintf('json-content/%s.json', self::FAQ_CACHE_KEY);
        if (!Storage::disk('s3')->has($filePath)) {
            return null;
        }

        return Storage::disk('s3')->get($filePath);
    }
}
