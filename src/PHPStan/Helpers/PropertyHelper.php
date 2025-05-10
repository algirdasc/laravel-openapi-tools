<?php

namespace OpenApiTools\PHPStan\Helpers;

class PropertyHelper
{
    public static function prepareName(string $propertyName): string
    {
        return str_replace(['.'], ' ', $propertyName);
    }
}