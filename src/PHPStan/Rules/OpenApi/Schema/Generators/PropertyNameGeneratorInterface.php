<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema\Generators;

use PhpParser\Node\Stmt;

interface PropertyNameGeneratorInterface
{
    public function generatePropertyName(string $property): string;

    public function isDateProperty(string $property): bool;
}
