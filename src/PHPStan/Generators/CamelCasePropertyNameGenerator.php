<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Generators;

use Illuminate\Support\Str;
use OpenApiTools\PHPStan\Helpers\PropertyHelper;

class CamelCasePropertyNameGenerator implements PropertyNameGeneratorInterface
{
    public function generatePropertyName(string $property): string
    {
        return Str::kebab(PropertyHelper::prepareName($property));
    }

    public function isDateProperty(string $property): bool
    {
        return str_ends_with($property, 'At');
    }

    public function isBooleanProperty(string $property): bool
    {
        return preg_match('/^(is|has)[A-Z]/', $property) !== false;
    }
}
