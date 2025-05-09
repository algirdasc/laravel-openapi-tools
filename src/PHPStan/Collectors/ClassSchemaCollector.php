<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Collectors;

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
 * @implements Collector<Node\Stmt\Class_, OperationAttribute|null>
 */
readonly class ClassSchemaCollector implements Collector
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    public function processNode(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        if (!$scope->isInClass()) {
            return null;
        }

        $className = (string) $node->namespacedName;

        /** @var ReflectionClass $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className)->getNativeReflection();

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attributeIdx => $attribute) {
                $resolvedAttributeName = $scope->resolveName($attribute->name);
                $attributeReflection = $this->reflectionProvider->getClass($resolvedAttributeName);

                if (!$attributeReflection->isSubclassOf(Schema::class)) {
                    continue;
                }

                /** @var Schema $schema */
                $schema = Attributes::getAttributes($classReflection, Schema::class)[$attributeIdx]->newInstance();

                return serialize(
                    new SchemaAttribute(
                        class: $className,
                        file: $scope->getFile(),
                        schema: $schema,
                        attribute: $attribute,
                    )
                );
            }
        }

        return null;
    }
}
