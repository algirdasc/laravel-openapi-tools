<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Collectors\FormRequestArrayCollector;
use OpenApiTools\PHPStan\Rules\Laravel\AbstractLaravelRule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<CollectedDataNode>
 */
class ValidateFormRequestRule extends AbstractLaravelRule implements Rule
{
    public function getValidatorTag(): string
    {
        return 'laravel.form_request';
    }

    public function getCollector(): string
    {
        return FormRequestArrayCollector::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        return $this->validateCollector($node);
    }
}
