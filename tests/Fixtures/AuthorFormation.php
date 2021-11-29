<?php

namespace Dillingham\Formation\Tests\Fixtures;

use Dillingham\Formation\Filter;
use Dillingham\Formation\Formation;
use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Dillingham\Formation\Tests\Fixtures\Models\User;

class AuthorFormation extends Formation
{
    public $model = User::class;

    public $foreignKey = 'author_id';
}
