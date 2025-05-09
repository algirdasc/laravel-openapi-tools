<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Collectors;

use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use OpenApiTools\PHPStan\Helpers\Attributes;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @implements Collector<Node\Stmt\Class_, string|null>
 */
readonly class SchemaCollector implements Collector
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

        $class = (string) $node->namespacedName;

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                $resolvedAttributeName = $scope->resolveName($attribute->name);
                $attributeReflection = $this->reflectionProvider->getClass($resolvedAttributeName);

                if (!$attributeReflection->isSubclassOf(Schema::class)) {
                    continue;
                }

                /** @var Schema $schema */
                $schema = $attributeReflection->getNativeReflection()->newInstance();

                return serialize(
                    new SchemaAttribute(
                        class: $class,
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
