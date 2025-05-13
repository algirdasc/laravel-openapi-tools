<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data\Parameter;

use OpenApi\Attributes as OA;

#[OA\PathParameter(
    name: 'parameter',
    schema: new OA\Schema(
        type: 'integer',
    )
)]
class ParameterWithSchema
{

}