<?php

declare(strict_types=1);

namespace Rules\Rules;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\RequestBody;
use OpenApi\Generator;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @implements Rule<Stmt\Class_>
 */
readonly class ValidateLaravelControllerRule extends AbstractOpenApiRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
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
        $errors = [];

        $className = (string) $node->namespacedName;
        if (!str_ends_with($className, 'Controller')) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($className)->getNativeReflection();
        foreach ($classReflection->getMethods() as $reflectionMethod) {
            $errors = [
                ...$errors,
                ...$this->validateFormRequest($reflectionMethod),
            ];
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    private function validateFormRequest(ReflectionMethod $reflection): array
    {
        $errors = [];

        $attributes = $this->getOpenApiAttributesFromMethod($reflection);
        if ($attributes === null) {
            return $errors;
        }

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $parameterReflection = $this->reflectionProvider->getClass($type->getName());
            if (!$parameterReflection->isSubclassOf(FormRequest::class)) {
                continue;
            }

            $requestBody = $attributes['requestBody'] ?? null;
            $parameters = $attributes['parameters'] ?? null;
            $queryParameters = $attributes['queryParameters'] ?? null;
            $operation = $attributes['_operation'] ?? null;
            if ($requestBody === null && !$operation instanceof Get) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'Missing "requestBody" schema property for method "%s" with FormRequest parameter type "%s"',
                        $reflection->getName(),
                        $parameter->getName()
                    )
                )
                    ->identifier('openApi.missingRequestBodyOnFormRequestMethod')
                    ->build();
            }

            if ($requestBody === null && $parameters === null && $queryParameters === null && $operation instanceof Get) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'Missing "parameters" / "queryParameters" / "requestBody" schema properties for method "%s" with FormRequest parameter type "%s"', // @phpcs:ignore
                        $reflection->getName(),
                        $parameter->getName()
                    )
                )
                    ->identifier('openApi.missingRequestBodyOrParametersOnFormRequestGetMethod')
                    ->build();
            }

            if (!$requestBody instanceof RequestBody) {
                continue;
            }

            $contentReference = Generator::isDefault($requestBody->content)
                ? ($requestBody->_unmerged[0]->ref ?? null)
                : ($requestBody->content->ref ?? null);

            if ($contentReference !== $parameterReflection->getName()) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'RequestBody reference type "%s" does not match method parameter type "%s"',
                        $contentReference,
                        $parameterReflection->getName()
                    )
                )
                    ->identifier('openApi.requestBodyDoesNotMatchMethodParameter')
                    ->build();
            }
        }

        return $errors;
    }
}
