<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest\Data;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [],
)]
class MissingSchemaPropertiesDataClass extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}