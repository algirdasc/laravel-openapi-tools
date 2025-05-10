<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/class-tags',
    tags: [],
)]
class TagCountDataClass
{
    #[OA\Delete(
        path: '/method1-tags',
        tags: []
    )]
    public function method1(): void
    {
    }

    #[OA\Post(
        path: '/method2-tags'
    )]
    public function method2(): void
    {
    }
}