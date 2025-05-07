<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Helpers;

use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;

class Attributes
{
    /**
     * @return list<ReflectionAttribute>
     */
    static public function getAttributes(ReflectionClass|ReflectionMethod $reflection, string $attributeName): array
    {
        try {
            $attributes = $reflection->getAttributes($attributeName, ReflectionAttribute::IS_INSTANCEOF);
        } catch (IdentifierNotFound $e) {
            $attributes = [];
        }

        return $attributes;
    }
}
