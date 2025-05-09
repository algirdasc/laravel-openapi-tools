<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\SchemaCollector;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class OperationPathRule implements Rule
{
    use IteratesOverCollection;

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        $errors = [];

        /** @var SchemaAttribute $schemaAttribute */
        foreach ($this->getIterator($node, SchemaCollector::class) as $schemaAttribute) {
            $a = 0;
        }

        return $errors;
    }
}
