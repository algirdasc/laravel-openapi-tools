<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Collectors;

use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use OpenApiTools\PHPStan\Helpers\Attributes;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @implements Collector<Node\Stmt\ClassMethod, string|null>
 */
readonly class OperationCollector implements Collector
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Node\Stmt\ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        if (!$scope->isInClass()) {
            return null;
        }

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                $resolvedAttributeName = $scope->resolveName($attribute->name);
                $attributeReflection = $this->reflectionProvider->getClass($resolvedAttributeName);

                if (!$attributeReflection->isSubclassOf(Operation::class)) {
                    continue;
                }

                /** @var Operation $operation */
                $operation = $attributeReflection->getNativeReflection()->newInstance();

                return serialize(
                    new OperationAttribute(
                        class: $scope->getClassReflection()->getName(),
                        file: $scope->getFile(),
                        operation: $operation,
                        attribute: $attribute,
                    )
                );
            }
        }

        return null;
    }
}
