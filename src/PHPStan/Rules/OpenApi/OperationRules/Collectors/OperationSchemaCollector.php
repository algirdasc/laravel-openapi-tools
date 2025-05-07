<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Collectors;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

class OperationSchemaCollector implements Collector
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
    }
}
