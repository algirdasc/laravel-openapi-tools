<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Collectors;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\Attributes;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @implements Collector<Node\Stmt\ClassMethod, string|null>
 */
readonly class MethodOperationCollector implements Collector
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Node\Stmt\ClassMethod::class;
    }

    /**
     * @throws \ReflectionException
     */
    public function processNode(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        if (!$scope->isInClass()) {
            return null;
        }

        $className = $scope->getClassReflection()->getName();
        $methodName = $node->name->toString();
        /** @var ReflectionClass $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $methodReflection = $classReflection->getMethod($methodName);

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attributeIdx => $attribute) {
                $resolvedAttributeName = $scope->resolveName($attribute->name);
                $attributeReflection = $this->reflectionProvider->getClass($resolvedAttributeName);

                if ($resolvedAttributeName !== Operation::class && !$attributeReflection->isSubclassOf(Operation::class)) {
                    continue;
                }

                /** @var Operation $operation */
                $operation = Attributes::getAttributes($methodReflection, Operation::class)[$attributeIdx]->newInstance();

                return serialize(
                    new OperationAttribute(
                        class: $className,
                        method: $methodName,
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
