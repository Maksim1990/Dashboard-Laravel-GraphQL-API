<?php

namespace App\Models;

use App\GraphQL\Traits\PaginatorResponse;
use App\Models\Traits\ResolveSearchableIndex;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use MongoDB\BSON\UTCDateTime;
use Spatie\QueryBuilder\QueryBuilder;

class Post extends Eloquent
{
    use HybridRelations;
    use HasFactory;
    use Searchable;
    use ResolveSearchableIndex;
    use PaginatorResponse;

    protected $fillable = [
        'title',
        'user_id',
        'description',
        'image_link',
        'type',
        'short_description'
    ];

    protected $appends = ['created_ago'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAgoAttribute(): string
    {
        return Carbon::instance($this->created_at->toDateTime())->diffForHumans();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function getDocumentId()
    {
        return $this->getAttribute("_id");
    }

    public function searchableAs()
    {
        return sprintf('posts-%s', $this->getSearchableIndexSuffix(config('app.env')));
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->_id,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'created_at' => $this->created_at,
        ];
    }

    public function scopeOrderAndFilter($query)
    {
        $orderBy = 'created_at';
        return $query->orderBy($orderBy, 'desc');
    }

    public function getPost($root, array $args): ?Model
    {
        $cacheKey = sprintf('post_%s_%s', $args['_id'], $args['user_id_open_post'] ?? null);
        return Cache::tags($cacheKey)
            ->remember(
                $cacheKey,
                now()->addMonth(),
                function () use ($args) {
                    $post = QueryBuilder::for(Post::class)->where('_id', $args['_id'])->first();
                    $post->user_id_open_post = $args['user_id_open_post'] ?? null;
                    return $post;
                }
            );
    }

    public function allPosts($root, array $args)
    {
        $first = (int)$args['first'] ?? 10;
        $page = (int)$args['page'] ?? 1;
        $orderBy = $args['orderBy'][0] ?? ['column' => 'created_at', 'order' => 'ASC'];
        $params = $args['params'] ?? [];

        if (($params['useCache'] ?? false)) {
            return Cache::tags(['posts'])
                ->remember(
                    sprintf(
                        'posts_%s_%s_%s_%s_%s_%s_%s',
                        $first,
                        $page,
                        $orderBy['column'],
                        $orderBy['order'],
                        $params['type'],
                        $params['onlyBookmarks'],
                        $params['category_id'],
                    ),
                    now()->addMonth(),
                    function () use ($first, $page, $orderBy, $params) {
                        return $this->getPosts($first, $page, $orderBy, $params, true);
                    }
                );
        }

        return $this->getPosts($first, $page, $orderBy, $params);
    }

    private function getPosts(
        int   $first,
        int   $page,
        array $orderBy,
        array $params,
        bool  $useCache = false
    ): array
    {
        $posts = QueryBuilder::for(Post::class);
        //-- Get posts by specific type
        if (isset($params['type']) && $params['type'] !== 'all') {
            $posts->where("type", $params['type']);
        }

        //-- Only bookmarked posts
        if (isset($params['onlyBookmarks']) && Auth::check()) {
            $bookmarkIDs = Bookmark::where('user_id', Auth::id())->pluck('post_id')->toArray();
            if ($params['onlyBookmarks']) {
                $posts->whereIn("_id", $bookmarkIDs);
            }
        }

        //-- Only posts in category
        if (isset($params['category_id']) && !is_null($params['category_id'])) {
            $postsIDs = CategoryPosts::where('category_id',
                $params['category_id'])->pluck('post_id')->toArray();
            $posts->whereIn("_id", $postsIDs);
        }

        return $this->getAbstractPaginatorResponse(
            $posts->orderBy($orderBy['column'], $orderBy['order'])->paginate(perPage: $first, page: $page),
            $first,
            $useCache
        );
    }
}
