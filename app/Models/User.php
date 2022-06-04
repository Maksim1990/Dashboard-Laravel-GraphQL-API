<?php

namespace App\Models;

use App\GraphQL\Traits\PaginatorResponse;
use App\Models\Traits\ResolveSearchableIndex;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Scout\Searchable;
use Spatie\QueryBuilder\QueryBuilder;

class User extends Eloquent implements AuthenticatableContract, CanResetPasswordContract
{
    use HybridRelations;
    use Authorizable;
    use AuthenticableTrait;
    use CanResetPassword;
    use Searchable;
    use HasFactory;
    use ResolveSearchableIndex;
    use PaginatorResponse;

    private const ELASTIC_DOCUMENT_TYPE = 'user';

    protected $connection = 'mongodb';
    protected $primaryKey = '_id';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'name',
        'email',
        'lastname',
        'country',
        'country_code',
        'city',
        'zip',
        'address',
        'phone',
        'birthdate',
        'avatar',
        'bio',
        'role',
        'enabled',
        'cognito_client_id',
    ];

    protected $appends = ['created_ago'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function searchableAs()
    {
        return sprintf('users-%s',  $this->getSearchableIndexSuffix(config('app.env')));
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->_id,
            'email' => $this->email,
            'name' => $this->name,
            'lastname' => $this->lastname,
        ];
    }

    public function getCreatedAgoAttribute(): string
    {
        return Carbon::instance($this->created_at->toDateTime())->diffForHumans();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function settings()
    {
        return $this->hasOne(Settings::class);
    }

    public function folders()
    {
        return $this->hasOne(UserCloudFolders::class);
    }

    public function images()
    {
        return $this->hasMany(UserImage::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarksData()
    {
        $posts_ids = $this->bookmarks()->get()->pluck('post_id')->toArray();
        return Post::whereIn('_id', $posts_ids)->get();
    }

    public function getDocumentId()
    {
        return $this->getAttribute("_id");
    }

    public function getProfileBackgroundAttribute()
    {
        $arrProfileBackground = UserImage::where('type', 'profile_background')->where('user_id',
            $this->getAttribute("_id"))->first();
        return (!is_null($arrProfileBackground)) ? $arrProfileBackground->url : null;
    }

    public function getBookmarkedPostsAttribute()
    {
        return $this->bookmarksData()->toArray();
    }

    public function setPasswordAttribute($password)
    {
        if (!empty($password)) {
            $this->attributes['password'] = bcrypt($password);
        }
    }

    public function getUser($root, array $args): ?Model
    {
        $cacheKey = sprintf('user_%s', $args['_id']);
        return Cache::tags($cacheKey)
            ->remember(
                $cacheKey,
                now()->addMonth(),
                function () use ($args) {
                    return QueryBuilder::for(User::class)->where('_id', $args['_id'])->first();
                }
            );
    }

    public function activeUsers($root, array $args): array
    {
        $isEnabled = $args['enabled'] ?? 'all';
        $isExcludeAuthUser = $args['params']['isExcludeAuthUser'] ?? true;
        $first = (int)$args['first'] ?? 10;
        $page = (int)$args['page'] ?? 1;
        $orderBy = $args['orderBy'][0] ?? ['column' => 'created_at', 'order' => 'ASC'];

        if (($args['params']['useCache'] ?? false) && Gate::forUser(Auth::user())->allows('use-cache')) {
            return Cache::tags(['users'])
                ->remember(
                    sprintf(
                        'users_%s_%s_%s_%s_%s_%s',
                        $isExcludeAuthUser,
                        $isEnabled,
                        $first,
                        $page,
                        $orderBy['column'],
                        $orderBy['order']
                    ),
                    now()->addMonth(),
                    function () use ($isExcludeAuthUser, $isEnabled, $first, $page, $orderBy) {
                        return $this->getUsersList(
                            $isExcludeAuthUser,
                            $isEnabled,
                            $first,
                            $page,
                            $orderBy,
                            true
                        );
                    }
                );
        }

        return $this->getUsersList($isExcludeAuthUser, $isEnabled, $first, $page, $orderBy);
    }

    private function getUsersList(
        ?bool   $isExcludeAuthUser,
        ?string $isEnabled,
        int     $first,
        int     $page,
        array   $orderBy,
        bool    $useCache = false
    ): array
    {
        $users = QueryBuilder::for(User::class);

        if ($isEnabled !== null && $isEnabled !== 'all') {
            $users->where('enabled', $isEnabled === 'enabled');
        }

        if ($isExcludeAuthUser) {
            $users->where("_id", "!=", Auth::id());
        }

        return $this->getAbstractPaginatorResponse(
            $users->orderBy($orderBy['column'], $orderBy['order'])->paginate(perPage: $first, page: $page),
            $first,
            $useCache
        );
    }
}
