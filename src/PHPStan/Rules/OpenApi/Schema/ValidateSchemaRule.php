<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Helpers\Attributes;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Node\Stmt\Class_>
 */
readonly class ValidateSchemaRule implements Rule
{
    public function __construct(
        protected ReflectionProvider $reflectionProvider,
        protected Container          $container
    ) {
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return [];
        }

        $className = (string) $node->namespacedName;

        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $classAttributes = Attributes::getAttributes($reflectionClass, Schema::class);

        return $this->validateAttributes($node, $classAttributes);
    }

    /**
     * @param list<ReflectionAttribute> $attributes
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    protected function validateAttributes(Node\Stmt\Class_ $node, array $attributes): array
    {
        $errors = [];

        /** @var list<ValidatorInterface> $validators */
        $validators = $this->container->getServicesByTag('openApiTools.validators.openapi.schema');
        foreach ($attributes as $attribute) {
            /** @var Schema $schemaInstance */
            $schemaInstance = $attribute->newInstance();
            foreach ($validators as $validator) {
                $errors = [
                    ...$errors,
                    ...$validator->validate($node, $schemaInstance),
                ];
            }
        }

        return $errors;
    }
}
