<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/class',
)]
class InvokeWithMethodsControllerDataClass
{
    use AuthorizesRequests;

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