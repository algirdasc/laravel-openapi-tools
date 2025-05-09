<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Annotations\Operation;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\OperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\NodeHelper;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class SummaryRule implements Rule
{
    use IteratesOverCollection;

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    )
    {
    }

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        $errors = [];

        /** @var OperationAttribute $operationAttribute */
        foreach ($this->getIterator($node, OperationCollector::class) as $operationAttribute) {
            $operation = $operationAttribute->getOperation();
            $summaryNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'summary');

            $summary = !Generator::isDefault($operation->summary) ? $operation->summary : '';

            if (strlen($summary) < 10) {
                $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" summary is too short, must be at least 10 chars', $operation->path))
                    ->identifier(RuleIdentifier::identifier('operationSummaryTooShort'))
                    ->file($operationAttribute->getFile())
                    ->line($summaryNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            if (strlen($summary) > 64) {
                $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" summary is too long, must be up to 64 chars', $operation->path))
                    ->identifier(RuleIdentifier::identifier('operationSummaryTooLong'))
                    ->file($operationAttribute->getFile())
                    ->line($summaryNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
