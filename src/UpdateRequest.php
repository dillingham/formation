<?php

namespace Dillingham\Formation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class UpdateRequest extends FormRequest
{
    public array $rules;

    public function validated(): array
    {
        if (empty($this->rules)) {
            return Request::all();
        }

        return parent::validated();
    }

    public function rules(): array
    {
        $this->rules = app(Manager::class)
            ->formation()
            ->rulesForUpdating();

        return $this->rules;
    }
}
