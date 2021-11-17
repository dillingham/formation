<?php

namespace Dillingham\Formation\Tests\Fixtures\Models;

use Dillingham\Formation\Tests\Fixtures\Database\Factories\PostFactory;
use Dillingham\Formation\Tests\Fixtures\Models\Comment;
use Dillingham\Formation\Tests\Fixtures\Models\Like;
use Dillingham\Formation\Tests\Fixtures\Models\PostTag;
use Dillingham\Formation\Tests\Fixtures\Models\Tag;
use Dillingham\Formation\Tests\Fixtures\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use function Dillingham\Formation\Tests\Fixtures\auth;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $guarded = [];

    public $casts = [
        'published_at' => 'date',
    ];

    public function like()
    {
        return $this->hasOne(Like::class)
            ->where('user_id', Auth::id());
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->using(PostTag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public static function newFactory()
    {
        return PostFactory::new();
    }

    public function scopeActive($query)
    {
        $query->where('status', 'active');
    }

    public function scopeActiveBoolean($query, $isActive)
    {
        $status = $isActive ? 'active' : 'inactive';

        $query->where('status', 'active');
    }

    public function scopeStatus($query, $status)
    {
        $query->where('status', $status);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
