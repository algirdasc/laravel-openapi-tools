<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApiTools\PHPStan\Collectors\SchemaCollector;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

class BooleanPropertyRule implements Rule
{
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $collectedData = $node->get(SchemaCollector::class);

        $a = 0;

        return [];
    }
}
