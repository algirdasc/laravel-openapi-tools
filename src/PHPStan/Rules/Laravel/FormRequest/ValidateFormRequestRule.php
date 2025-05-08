<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Rules\Laravel\AbstractLaravelRule;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Collectors\FormRequestArrayCollector;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;

class ValidateFormRequestRule extends AbstractLaravelRule implements Rule
{
    /**
     * @var list<IdentifierRuleError>
     */
    protected array $errors = [];


    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

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
