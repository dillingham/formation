<?php

namespace Dillingham\ListRequest\Tests\Fixtures;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Like extends Pivot
{
    public $table = 'likes';

    public $guarded = [];
}
