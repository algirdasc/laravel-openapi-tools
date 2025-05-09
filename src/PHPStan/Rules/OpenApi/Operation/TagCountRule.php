<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

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
readonly class TagCountRule implements Rule
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
            $tagNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'tags');

            $tags = is_array($operation->tags) ? $operation->tags : [];

            if (count($tags) === 0) {
                $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must have at least 1 tag', $operation->path))
                    ->identifier(RuleIdentifier::identifier('operationTagCountIncorrect'))
                    ->file($operationAttribute->getFile())
                    ->line($tagNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
