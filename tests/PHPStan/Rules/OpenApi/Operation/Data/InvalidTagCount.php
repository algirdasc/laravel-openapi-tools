<?php

namespace Tests\PHPStan\Rules\OpenApi\Operation\Data;

use OpenApi\Attributes as OA;

class InvalidTagCount
{
    #[OA\Get(
        tags: []
    )]
    public function emptyTags(): void
    {
    }

    #[OA\Post()]
    public function undefinedTags(): bool
    {
        $a = 0;

        if ($a === 0) {
            return true;
        }

        return false;
    }
}