<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\NodeHelper;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class DescriptionRule implements Rule
{
    use IteratesOverCollection;

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        /** @var OperationAttribute $operationAttribute */
        foreach ($this->getIterator($node, [MethodOperationCollector::class, ClassOperationCollector::class]) as $operationAttribute) {
            $operation = $operationAttribute->getOperation();
            $operationName = sprintf('%s %s', strtoupper($operation->method), $operation->path);

            $description = !Generator::isDefault($operation->description) ? $operation->description : '';
            $descriptionNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'description');

            if (strlen($description) < 20) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" description is too short, must be at least 20 chars', $operationName))
                    ->identifier(RuleIdentifier::identifier('operationDescriptionTooShort'))
                    ->file($operationAttribute->getFile())
                    ->line($descriptionNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
