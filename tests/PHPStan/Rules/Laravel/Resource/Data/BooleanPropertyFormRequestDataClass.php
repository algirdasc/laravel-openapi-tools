<?php

namespace Tests\PHPStan\Rules\Laravel\Resource\Data;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
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