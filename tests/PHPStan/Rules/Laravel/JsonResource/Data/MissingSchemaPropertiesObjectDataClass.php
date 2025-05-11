<?php

namespace Tests\PHPStan\Rules\Laravel\JsonResource\Data;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema]
class MissingSchemaPropertiesObjectDataClass extends JsonResource
{

}