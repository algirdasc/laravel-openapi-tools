<?php

namespace Tests\PHPStan\Rules\Laravel\JsonResource\Data;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property('boolean_1', type: 'boolean'),
        new OA\Property('object', properties: [
            new OA\Property('boolean_2', type: 'boolean')
        ], type: 'object'),
    ]
)]
class BooleanPropertyJsonResourceDataClass extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [];
    }
}