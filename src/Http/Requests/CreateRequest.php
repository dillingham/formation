<?php

namespace Dillingham\Formation\Http\Requests;

use Dillingham\Formation\Manager;
use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return app(Manager::class)
            ->formation()
            ->rulesForCreating();
    }
}
