<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Helpers;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;

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

    public static function findFromNodeByName(Attribute $node, string $name): ?ArrayItem
    {
        foreach ($node->args as $arg) {
            if ($arg->name?->name !== 'properties') {
                continue;
            }

            if (!$arg->value instanceof ArrayItem) {
                continue;
            }

            foreach ($arg->value->items as $property) {
                if ($property->value->args[0]->value->value === $name) {
                    return $property;
                }
            }
        }

        return null;
    }
}
