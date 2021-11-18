<?php

namespace Dillingham\Formation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Dillingham\Formation\Manager;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return app(Manager::class)
            ->formation()
            ->rulesForUpdating();
    }
}
