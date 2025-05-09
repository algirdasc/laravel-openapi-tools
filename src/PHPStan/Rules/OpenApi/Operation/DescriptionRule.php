<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Attributes\Parameter;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\OperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
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
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        $errors = [];

        /** @var OperationAttribute $operationAttribute */
        foreach ($this->getIterator($node, OperationCollector::class) as $operationAttribute) {
            $operation = $operationAttribute->getOperation();

            $description = !Generator::isDefault($operation->description) ? $operation->description : '';

            if (strlen($description) < 20) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf('Path "%s" description is too short, must be at least 20 chars', $operation->path)
                )
                    ->identifier(RuleIdentifier::identifier('operationDescriptionTooShort'))
                    ->build();
            }
        }

        return $errors;
    }
}
