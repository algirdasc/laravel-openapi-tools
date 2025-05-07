<?php

declare(strict_types=1);

namespace Rules\Rules\OperationRules;

use PhpParser\Node;
use Rules\Rules\AbstractOpenApiRule;
use Rules\Rules\OperationRules\Validators\PathValidator;

class ValidateOperationRule extends AbstractOpenApiRule implements Rule
{
    private const array VALIDATORS = [

    ];

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
