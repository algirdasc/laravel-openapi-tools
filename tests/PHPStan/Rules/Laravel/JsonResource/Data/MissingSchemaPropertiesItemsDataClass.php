<?php

namespace Tests\PHPStan\Rules\Laravel\JsonResource\Data;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'array',
    items: new OA\Items(
        properties: [],
    )
)]
class MissingSchemaPropertiesItemsDataClass extends JsonResource
{

}