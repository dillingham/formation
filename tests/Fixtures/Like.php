<?php

namespace Dillingham\Formation\Tests\Fixtures;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Like extends Pivot
{
    public $table = 'likes';

    public $guarded = [];
}
