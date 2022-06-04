<?php
namespace App\Models;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Spatie\QueryBuilder\QueryBuilder;

class Category extends Eloquent
{
    use HybridRelations;

    protected $fillable = [
        'title','description','created_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,
            "created_by");
    }

    public function getDocumentId(){
        return $this->getAttribute("_id");
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $resolveInfo
     * @return Builder
     */
    public function allCategories($root, array $args, $context, ResolveInfo $resolveInfo): QueryBuilder
    {
        $offset = $args['offset'] ?? 0;
        $number = $args['number'] ?? 10;
        $orderBy = $args['orderBy'][0] ?? [
                'field'=>'created_at',
                'order'=>'DESC',
            ];

        $categories=QueryBuilder::for(Category::class);
        //-- Get categories by specific type
        $categories->orderBy($orderBy['field'],$orderBy['order'])->skip($offset)->take($number);

        return $categories;
    }

    public function categoryPosts()
    {
        return $this->hasMany(CategoryPosts::class);
    }

    public function postsData()
    {
        $posts_ids = $this->categoryPosts()->get()->pluck('post_id')->toArray();
        return Post::whereIn('_id', $posts_ids)->get();
    }

    public function getPostsAttribute()
    {
        return $this->postsData()->toArray();
    }
}
