<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Rules\OpenApi\AbstractOpenApiRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface as OperationValidatorInterface;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators\DescriptionValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators\PathParameterValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators\PathValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators\SummaryValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators\TagCountValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\ValidatorInterface as SchemaValidatorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

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

        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $classAttributes = Attributes::getAttributes($reflectionClass, Operation::class);
        $errors = $this->validateAttributes($classAttributes);

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $methodAttributes = Attributes::getAttributes($reflectionMethod, Operation::class);
            $errors = [
                ...$errors,
                ...$this->validateAttributes($methodAttributes),
            ];
        }

        return $errors;
    }

    /**
     * @param array<ReflectionAttribute> $attributes
     * @return array<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    protected function validateAttributes(array $attributes): array
    {
        $errors = [];

        /**
         * @var array<SchemaValidatorInterface|OperationValidatorInterface> $validators
         */
        $validators = $this->container->getServicesByTag('openApiTools.validators.openapi.operation');
        foreach ($attributes as $attribute) {
            $schemaInstance = $attribute->newInstance();
            foreach ($validators as $validator) {
                $errors = [
                    ...$errors,
                    ...$validator->validate($schemaInstance),
                ];
            }
        }

        return $errors;
    }
}
