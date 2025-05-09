<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

class OperationAttributeOnInvokeMethod
{
    #[OA\Get()]
    public function __invoke(): void
    {
    }
}