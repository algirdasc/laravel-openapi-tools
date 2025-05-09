<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApiTools\PHPStan\Collectors\JsonResourceArrayCollector;
use OpenApiTools\PHPStan\Rules\Laravel\AbstractLaravelRule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<CollectedDataNode>
 */
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

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        return $this->validateCollector($node);
    }
}
