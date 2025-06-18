<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;
use Tests\PHPStan\Rules\OpenApi\Operation\Data\Response\OtherResponseClass;
use Tests\PHPStan\Rules\OpenApi\Operation\Data\Response\ResponseClass;

class ResponseTypeDataClass
{
    #[OA\Post(
        path: '/method1',
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(ref: OtherResponseClass::class, response: 202),
            new OA\Response(response: 401),
            new OA\Response(response: 500),
        ]
    )]
    public function method1(): ?ResponseClass
    {
        return new ResponseClass();
    }

    #[OA\Post(
        path: '/success1',
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(ref: ResponseClass::class, response: 202),
            new OA\Response(response: 401),
            new OA\Response(response: 422),
            new OA\Response(response: 500),
        ]
    )]
    public function success1(): ?ResponseClass
    {
        return null;
    }

    #[OA\Get(
        path: '/success2',
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(response: 201),
        ],
    )]
    public function success2(): int
    {
        return 0;
    }

    #[OA\Get(
        path: '/success3',
        responses: [
            new OA\Response(response: 202, content: new OA\JsonContent()),
        ],
    )]
    public function success3(): int|string
    {
        return 0;
    }

    #[OA\Get(
        path: '/success4',
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property('data', ref: ResponseClass::class),
                    ]
                ),
            ),
            new OA\Response(response: 401),
            new OA\Response(response: 500),
        ]
    )]
    public function success4(): ?ResponseClass
    {
        return new ResponseClass();
    }
}