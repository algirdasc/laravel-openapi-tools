<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

class InvokeControllerDataClass
{
    #[OA\Delete(
        path: '/invoke',
        tags: []
    )]
    public function __invoke(): void
    {
    }
}