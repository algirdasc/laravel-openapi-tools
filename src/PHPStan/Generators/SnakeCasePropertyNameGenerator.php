<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Generators;

use Illuminate\Support\Str;
use OpenApiTools\PHPStan\Helpers\PropertyHelper;

class SnakeCasePropertyNameGenerator implements PropertyNameGeneratorInterface
{
    public function generatePropertyName(string $property): string
    {
        return Str::snake(PropertyHelper::prepareName($property));
    }

    public function isDateProperty(string $property): bool
    {
        return str_ends_with($property, '_at');
    }

    public function isBooleanProperty(string $property): bool
    {
        return str_starts_with($property, 'is_') || str_starts_with($property, 'has_');
    }
}
