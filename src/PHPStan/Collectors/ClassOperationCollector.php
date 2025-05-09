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
 * @implements Collector<Node\Stmt\Class_, OperationAttribute|null>
 */
readonly class ClassOperationCollector implements Collector
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    /**
     * @throws \ReflectionException
     */
    public function processNode(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $className = (string) $node->namespacedName;
        /** @var ReflectionClass $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className)->getNativeReflection();

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attributeIdx => $attribute) {
                $resolvedAttributeName = $scope->resolveName($attribute->name);
                $attributeReflection = $this->reflectionProvider->getClass($resolvedAttributeName);

                if (!$attributeReflection->isSubclassOfClass($this->reflectionProvider->getClass(Operation::class))) {
                    continue;
                }

                /** @var Operation $operation */
                $operation = Attributes::getAttributes($classReflection, Operation::class)[$attributeIdx]->newInstance();

                return serialize(
                    new OperationAttribute(
                        class: $className,
                        method: '__invoke',
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
