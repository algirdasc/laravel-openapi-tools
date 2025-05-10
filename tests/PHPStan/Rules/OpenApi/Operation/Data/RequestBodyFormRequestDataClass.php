<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;
use Tests\PHPStan\Rules\OpenApi\Operation\Data\Request\SomeRequest;

#[OA\Post(
    path: '/class',
)]
class RequestBodyFormRequestDataClass
{
    public function __invoke(SomeRequest $request): void
    {
    }

    #[OA\Post(
        path: '/method1',
    )]
    public function method1(SomeRequest $request): void
    {
    }

    #[OA\Post(
        path: '/method2',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: SomeRequest::class),
        )
    )]
    public function method2(SomeRequest $request): void
    {
    }

    #[OA\Post(
        path: '/method3',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: 'something'),
        )
    )]
    public function method3(SomeRequest $request): void
    {
    }

    #[OA\Get(
        path: '/method4',
    )]
    public function method4(SomeRequest $request): void
    {
    }

    #[OA\Post(
        path: '/method5',
    )]
    public function method5(): void
    {
    }
}