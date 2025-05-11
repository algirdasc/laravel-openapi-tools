<?php

namespace Tests\PHPStan\Rules\Laravel\JsonResource\Data;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property('boolean_1', properties: [
            new OA\Property('boolean_2', type: 'boolean')
        ], type: 'boolean'),
    ]
)]
class BooleanPropertyFormRequestDataClass extends FormRequest
{

}