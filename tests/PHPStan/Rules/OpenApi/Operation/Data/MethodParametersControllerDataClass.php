<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/invoke/{parameter1}/{parameter2}',
)]
class MethodParametersControllerDataClass
{
    public function __invoke(int $parameter2, bool $parameter1): void
    {
    }

    #[OA\Get(
        path: '/method1/{parameter1}/{parameter2}',
    )]
    public function method1(int $parameter2, int $parameter1): void
    {
    }

    #[OA\Get(
        path: '/method2/{parameter1}',
    )]
    public function method2(string $parameter2): void
    {
    }

    #[OA\Get(
        path: '/method3/{parameter2}',
    )]
    public function method3(string $parameter1): void
    {
    }

    #[OA\Get(
        path: '/method4/{parameter}',
        parameters: [
            new OA\PathParameter(name: 'parameter'),
        ],
    )]
    public function method4(): void
    {
    }

    #[OA\Get(
        path: '/success1',
    )]
    public function success1(): void
    {
    }

    #[OA\Get(
        path: '/success2/{parameter1}/{parameter2}',
    )]
    public function success2(string $parameter1, FormRequest $request, string $parameter2): void
    {
    }
}