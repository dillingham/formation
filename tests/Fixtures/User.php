<?php

namespace Dillingham\Formation\Tests\Fixtures;

use Dillingham\Formation\Tests\Fixtures\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    public $guarded = [];

    public static function newFactory()
    {
        return UserFactory::new();
    }
}
