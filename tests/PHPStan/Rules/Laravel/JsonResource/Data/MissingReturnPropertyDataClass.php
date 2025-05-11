<?php

namespace Tests\PHPStan\Rules\Laravel\JsonResource\Data;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property('schema-key2', properties: [
            new OA\Property('schema-key3')
        ]),
    ]
)]
class MissingReturnPropertyDataClass extends JsonResource
{
    public function toArray($request)
    {
        return [
            'return-key1' => [
                'return-key2' => 'value',
            ]
        ];
    }
}