<?php

namespace Dillingham\Formation\Http\Controllers;

use Illuminate\Support\Arr;

class SelectOptionController
{
    public function __invoke($resource)
    {
        $formation = Arr::get(config('formations.options'), $resource);

        abort_if(is_null($formation), 500, 'No select-option formation for: ' . $resource);

        return app($formation)->options()->results();
    }
}
