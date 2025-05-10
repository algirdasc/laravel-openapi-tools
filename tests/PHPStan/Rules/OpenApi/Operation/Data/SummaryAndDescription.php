<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/class',
    description: '',
    summary: '',
)]
class SummaryAndDescription
{
    #[OA\Delete(
        path: '/method1',
        description: 'too short',
        summary: 'too short'
    )]
    public function method1(): void
    {
    }

    #[OA\Delete(
        path: '/method2',
        description: 'this is description',
        summary: 'too loooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
    )]
    public function method2(): void
    {
    }

    #[OA\Delete(
        path: '/success',
        description: 'this description is just right',
        summary: 'this summary is just right',
    )]
    public function success(): void
    {
    }
}