<?php

namespace Dillingham\Formation\Tests\Fixtures;

use Dillingham\Formation\Tests\Fixtures\Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public $guarded = [];

    public static function newFactory()
    {
        return CommentFactory::new();
    }
}
