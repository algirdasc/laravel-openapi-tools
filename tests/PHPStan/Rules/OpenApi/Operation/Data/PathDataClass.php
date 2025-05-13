<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;
use Tests\PHPStan\Rules\OpenApi\Operation\Data\Parameter\Parameter;
use Tests\PHPStan\Rules\OpenApi\Operation\Data\Parameter\ParameterWithSchema;

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
            new OA\PathParameter(name: 'subparameter'),
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

    #[OA\Post(
        path: '/method6/{parameter_diff_name}',
        parameters: [
            new OA\PathParameter(ref: ParameterWithSchema::class),
        ]
    )]
    public function method6(): void
    {
    }

    #[OA\Get(
        path: '/success1/{parameter}',
        parameters: [
            new OA\PathParameter(name: 'parameter'),
        ]
    )]
    public function success1(): void
    {
    }

    #[OA\Get(
        path: '/success2/{parameter}',
        parameters: [
            new OA\PathParameter(ref: ParameterWithSchema::class),
        ]
    )]
    public function success2(): void
    {
    }
}