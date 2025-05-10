<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/class',
    responses: []
)]
class ResponsesDataClass
{
    #[OA\Get(
        path: '/method1',
        requestBody: new OA\RequestBody(),
        responses: [],
    )]
    public function method1(): void
    {
    }

    #[OA\Get(
        path: '/method2',
    )]
    public function method2(): void
    {
    }

    #[OA\Post(
        path: '/method3',
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(response: 202),
            new OA\Response(response: 401),
            new OA\Response(response: 500),
        ]
    )]
    public function method3(): void
    {
    }

    #[OA\Post(
        path: '/success',
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(response: 202),
            new OA\Response(response: 401),
            new OA\Response(response: 422),
            new OA\Response(response: 500),
        ]
    )]
    public function success(): void
    {
    }
}