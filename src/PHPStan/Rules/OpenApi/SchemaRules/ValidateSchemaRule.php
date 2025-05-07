<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\SchemaRules;

use OpenApiTools\PHPStan\Rules\OpenApi\AbstractOpenApiRule;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators\PathValidator;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

class ValidateSchemaRule extends AbstractOpenApiRule implements Rule
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    /**
     * @template T of ValidatorInterface
     * @return array<class-string<T>>
     */
    public function getValidators(): array
    {
        return [
            PathValidator::class,
        ];
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
        $className = (string) $node->namespacedName;

        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $errors = $this->validateOpenApiAttributes($reflectionClass);

        foreach ($reflectionClass->getMethods() as $method) {
            $errors = [
                ...$errors,
                ...$this->validateOpenApiAttributes($method),
            ];
        }

        return $errors;
    }
}
