<?php

declare(strict_types=1);

namespace Rules\Rules;

use OpenApi\Annotations\Operation;
use ReflectionMethod;

abstract class AbstractOpenApiRule
{
    /**
     * @return array<array-key, mixed>|null
     */
    protected function getOpenApiAttributesFromMethod(ReflectionMethod $reflection): ?array
    {
        $attributes = $reflection->getName() !== '__invoke'
            ? $reflection->getAttributes()
            : $reflection->getDeclaringClass()->getAttributes();

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if (!$attributeInstance instanceof Operation) {
                continue;
            }

            return [...$attribute->getArguments(), '_operation' => $attributeInstance];
        }

        return null;
    }

    abstract public function getValidators(): array;
}
