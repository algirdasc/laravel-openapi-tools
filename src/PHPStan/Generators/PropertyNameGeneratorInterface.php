<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Generators;

interface PropertyNameGeneratorInterface
{
    public function generatePropertyName(string $property): string;

    public function isDateProperty(string $property): bool;

    public function isBooleanProperty(string $property): bool;
}
