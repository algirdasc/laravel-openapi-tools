<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;
use Tests\PHPStan\Rules\OpenApi\Schema\Data\SchemalessDataClass;

#[OA\Get(
    path: '/class',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(ref: SchemalessDataClass::class),
    )
)]
class RequestBodyReferenceDataClass
{
    #[OA\Get(
        path: '/method1',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: SchemalessDataClass::class),
        ),
    )]
    public function method1(): void
    {
    }

    #[OA\Get(
        path: '/method2',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: 'something'),
        ),
    )]
    public function method2(): void
    {
    }

    #[OA\Post(
        path: '/method3',
    )]
    public function method3(): void
    {
    }
}