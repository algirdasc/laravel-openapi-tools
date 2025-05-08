<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApiTools\PHPStan\Rules\Laravel\AbstractLaravelRule;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\Collectors\JsonResourceArrayCollector;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

class ValidateResourceRule extends AbstractLaravelRule implements Rule
{
    public function getValidatorTag(): string
    {
        return 'laravel.resource';
    }

    public function getCollector(): string
    {
        return JsonResourceArrayCollector::class;
    }

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        return $this->validateCollector($node);
    }
}
