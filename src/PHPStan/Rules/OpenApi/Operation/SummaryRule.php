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
readonly class SummaryRule implements Rule
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

            $summaryNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'summary');

            $summary = !Generator::isDefault($operation->summary) ? $operation->summary : '';

            if (strlen($summary) < 10) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" summary is too short, must be at least 10 chars', $operationName))
                    ->identifier(RuleIdentifier::identifier('operationSummaryTooShort'))
                    ->file($operationAttribute->getFile())
                    ->line($summaryNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            if (strlen($summary) > 64) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" summary is too long, must be at to 64 chars', $operationName))
                    ->identifier(RuleIdentifier::identifier('operationSummaryTooLong'))
                    ->file($operationAttribute->getFile())
                    ->line($summaryNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
