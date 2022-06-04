<?php

namespace App\GraphQL\Queries;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class Chats
{
    public function __invoke($rootValue, array $args): Collection
    {
        $userId = $args['user_id'];
        $offset = (int)($args['offset'] ?? 0);
        $number = (int)($args['number'] ?? 10);
        $orderBy = $args['orderBy'][0] ?? ['field' => 'created_at', 'order' => 'ASC'];

        if (($args['useCache'] ?? false) && Gate::forUser(Auth::user())->allows('use-cache')) {
            return Cache::tags(['users'])
                ->remember(
                    sprintf(
                        'chats_%s_%s_%s_%s_%s',
                        $userId,
                        $offset,
                        $number,
                        $orderBy['column'],
                        $orderBy['order']
                    ),
                    now()->addMonth(),
                    function () use ($userId, $offset, $number, $orderBy) {
                        return $this->getChats($userId, $offset, $number, $orderBy);
                    }
                );
        }
        return $this->getChats($userId, $offset, $number, $orderBy);
    }

    private function getChats(string $userId, int $offset, int $number, array $orderBy){
        return Chat::where('user_id', $userId)->orWhere('from_user_id', $userId)
            ->orderBy($orderBy['column'], $orderBy['order'])->skip($offset)->take($number)->get();
    }
}
