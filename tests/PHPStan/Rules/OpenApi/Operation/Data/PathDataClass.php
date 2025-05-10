<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;
use Tests\PHPStan\Rules\OpenApi\Operation\Data\Parameter\Parameter;

#[OA\Get(
    path: '/class/',
)]
class PathDataClass
{
    #[OA\Delete(
        path: 'method1/',
    )]
    public function method1(): void
    {
    }

    #[OA\Delete(
        path: '/method2/{parameter}',
        parameters: [
            new OA\PathParameter('sub-parameter'),
        ],
    )]
    public function method2(): void
    {
    }

    #[OA\Post(
        path: '/method3'
    )]
    public function method3(string $missingParameter): void
    {
    }

    #[OA\Post(
        path: 'method4'
    )]
    public function method4(): void
    {
    }

    #[OA\Post(
        path: '/method5/{parameter}',
        parameters: [
            new OA\PathParameter(ref: Parameter::class),
        ]
    )]
    public function method5(): void
    {
    }

    #[OA\Delete(
        path: '/success/{parameter}',
        parameters: [
            new OA\PathParameter('parameter'),
        ]
    )]
    public function success(): void
    {
    }
}