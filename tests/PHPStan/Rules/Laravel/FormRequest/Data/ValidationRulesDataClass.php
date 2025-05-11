<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest\Data;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [],
)]
class ValidationRulesDataClass extends FormRequest
{
    public function rules(): array
    {
        return [
            'required-property-1' => ['required', 'string'],
            'required-property-2' => 'string|required',
            'nullable-property-1' => ['nullable'],
            'nullable-property-2' => 'nullable|string',
        ];
    }
}