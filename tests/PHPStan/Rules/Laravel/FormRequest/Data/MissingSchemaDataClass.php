<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest\Data;

use Illuminate\Foundation\Http\FormRequest;

class MissingSchemaDataClass extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}