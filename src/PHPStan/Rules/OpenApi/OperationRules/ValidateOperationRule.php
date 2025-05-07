<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\OperationRules;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Rules\OpenApi\AbstractOpenApiRule;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators\DescriptionValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators\PathParameterValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators\PathValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators\SummaryValidator;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators\TagCountValidator;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

class ValidateOperationRule extends AbstractOpenApiRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    /**
     * @template T of ValidatorInterface
     * @return array<class-string<T>>
     */
    public function getValidators(): array
    {
        return [
            DescriptionValidator::class,
            PathParameterValidator::class,
            PathValidator::class,
            SummaryValidator::class,
            TagCountValidator::class,
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
        if (!$node instanceof Stmt\Class_) {
            return [];
        }

        $className = (string) $node->namespacedName;

        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $classAttributes = Attributes::getAttributes($reflectionClass, Operation::class);
        $this->validateAttributes($classAttributes);

        foreach ($reflectionClass->getMethods() as $method) {
            $methodAttributes = Attributes::getAttributes($method, Operation::class);
            $this->validateAttributes($methodAttributes);
        }

        return $this->errors;
    }

    protected function getValidatorTag(): string
    {
        return 'openapi.operation';
    }
}
