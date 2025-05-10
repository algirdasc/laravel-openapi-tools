<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Helpers;

use PHPStan\BetterReflection\Reflection\Adapter\FakeReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;

class Attributes
{
    /**
     * @template TAttribute
     * @param class-string<TAttribute> $attributeName
     * @return array<ReflectionAttribute>
     */
    static public function getAttributes(ReflectionClass|ReflectionMethod $reflection, string $attributeName): array
    {
        try {
            /** @var array<ReflectionAttribute> $attributes */
            $attributes = $reflection->getAttributes($attributeName, ReflectionAttribute::IS_INSTANCEOF);
        } catch (IdentifierNotFound $e) {
            $attributes = [];
        }

        return $attributes;
    }

    /**
     * @template TAttribute
     * @param class-string<TAttribute> $attributeName
     * @return ReflectionAttribute|null
     */
    static public function getAttribute(ReflectionClass|ReflectionMethod $reflection, string $attributeName): ?ReflectionAttribute
    {
        return self::getAttributes($reflection, $attributeName)[0] ?? null;
    }
}
