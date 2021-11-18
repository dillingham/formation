<?php

namespace Dillingham\Formation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return app(Manager::class)
            ->formation()
            ->rulesForUpdating();
    }
}
