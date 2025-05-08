<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema\Generators;

use Illuminate\Support\Str;
use PhpParser\Node\Stmt;

class KebabCasePropertyNameGenerator implements PropertyNameGeneratorInterface
{
    public function generatePropertyName(string $property): string
    {
        return Str::kebab($property);
    }

    public function isDateProperty(string $property): bool
    {
        return str_ends_with($property, 'At');
    }
}
