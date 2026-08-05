<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/trait',
)]
class InvokeWithTraitControllerDataClass
{
    use AuthorizesRequests;

    public function __invoke(): void
    {
    }
}
