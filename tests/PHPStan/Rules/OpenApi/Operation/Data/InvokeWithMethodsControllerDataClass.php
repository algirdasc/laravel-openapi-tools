<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/class',
)]
class InvokeWithMethodsControllerDataClass
{
    public function __invoke(): void
    {
    }

    public function method1(): void
    {
    }

    private function method2(): void
    {
    }

    protected function method3(): void
    {
    }
}