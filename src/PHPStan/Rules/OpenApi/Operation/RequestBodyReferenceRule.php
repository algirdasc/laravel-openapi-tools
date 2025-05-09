<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\NodeHelper;
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
readonly class RequestBodyReferenceRule implements Rule
{
    use IteratesOverCollection;

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
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
        $errors = [];

        /** @var OperationAttribute $operationAttribute */
        foreach ($this->getIterator($node, [MethodOperationCollector::class, ClassOperationCollector::class]) as $operationAttribute) {
            $operation = $operationAttribute->getOperation();

            $requestBody = !Generator::isDefault($operation->requestBody) ? $operation->requestBody : null;
            if ($requestBody === null) {
                continue;
            }

            $requestBodyNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'requestBody');

            $contentReference = Generator::isDefault($requestBody->content)
                ? ($requestBody->_unmerged[0]->ref ?? null)
                : ($requestBody->content->ref ?? null);

            if ($contentReference === null || Generator::isDefault($contentReference)) {
                return $errors;
            }

            /**
             * @var ReflectionClass $reflection
             */
            $reflection = $this->reflectionProvider->getClass($contentReference)->getNativeReflection();
            $schema = Attributes::getAttributes($reflection, Schema::class);

            if (!$schema) {
                $errors[] = RuleErrorBuilder::message(sprintf('RequestBody reference "%s" does not have schema attribute', $contentReference))
                    ->identifier(RuleIdentifier::identifier('operationRequestBodyReferenceHasEmptySchema'))
                    ->file($operationAttribute->getFile())
                    ->line($requestBodyNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
