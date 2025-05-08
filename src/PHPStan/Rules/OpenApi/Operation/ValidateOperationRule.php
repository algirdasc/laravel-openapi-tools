<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Stmt\Class_>
 */
class ValidateOperationRule implements Rule
{
    public function __construct(
        protected ReflectionProvider $reflectionProvider,
        protected Container          $container
    ) {
    }

    public function getNodeType(): string
    {
        return Stmt\Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Stmt\Class_) {
            return [];
        }

        $className = (string) $node->namespacedName;

        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $classAttributes = Attributes::getAttributes($reflectionClass, Operation::class);
        $errors = $this->validateAttributes($reflectionClass, $classAttributes);

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $methodAttributes = Attributes::getAttributes($reflectionMethod, Operation::class);
            $errors = [
                ...$errors,
                ...$this->validateAttributes($reflectionMethod, $methodAttributes),
            ];
        }

        return $errors;
    }

    /**
     * @param list<ReflectionAttribute> $attributes
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    protected function validateAttributes(ReflectionClass|ReflectionMethod $reflection, array $attributes): array
    {
        $errors = [];

        /** @var list<ValidatorInterface> $validators */
        $validators = $this->container->getServicesByTag('openApiTools.validators.openapi.operation');
        foreach ($attributes as $attribute) {
            /** @var Operation $schemaInstance */
            $schemaInstance = $attribute->newInstance();
            foreach ($validators as $validator) {
                $errors = [
                    ...$errors,
                    ...$validator->validate($reflection, $schemaInstance),
                ];
            }
        }

        return $errors;
    }
}
