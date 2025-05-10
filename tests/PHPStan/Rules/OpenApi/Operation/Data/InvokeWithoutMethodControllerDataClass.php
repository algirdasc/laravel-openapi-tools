<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/invoke',
)]
class InvokeWithoutMethodControllerDataClass
{
    public function ___invoke(): void // invalid __invoke
    {
    }
}