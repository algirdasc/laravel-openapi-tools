<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Helpers;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

class SchemaProperties
{
    public static function findByName(Schema|Items $schema, string $name): ?Property
    {
        if (Generator::isDefault($schema->properties) || empty($schema->properties)) {
            return null;
        }

        /** @var Property $property */
        foreach ($schema->properties as $property) {
            if ($property->property === $name) {
                return $property;
            }
        }

        return null;
    }
}
