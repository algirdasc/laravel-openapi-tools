<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Collectors;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * @implements Collector<Node\Attribute, string|null>
 */
class SchemaCollector implements Collector
{
    public function getNodeType(): string
    {
        return Node\Attribute::class;
    }

    public function processNode(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Attribute) {
            return null;
        }

        if ($node->name->name !== Schema::class) {
            return null;
        }

        return serialize(
            new SchemaAttribute(
                class: $scope->getClassReflection()->getName(),
                file: $scope->getFile(),
                line: $node->getLine(),
                attribute: $node,
            )
        );
    }
}
