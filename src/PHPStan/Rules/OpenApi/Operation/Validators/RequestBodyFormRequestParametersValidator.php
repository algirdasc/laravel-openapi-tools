<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Get;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;
use Throwable;

readonly class RequestBodyFormRequestParametersValidator implements ValidatorInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];

        if ($reflection instanceof ReflectionClass) {
            try {
                $reflection = $reflection->getMethod('__invoke');
            } catch (Throwable) {
                return [
                    RuleErrorBuilder::message('Operation attribute applied to class, but "__invoke" method not found')
                        ->identifier(RuleIdentifier::identifier('classOperationAttributeWithoutInvokeMethod'))
                        ->build()
                ];
            }
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

            $requestBody = !Generator::isDefault($operation->requestBody) ? $operation->requestBody : null;

            if ($requestBody === null && !$operation instanceof Get) {
                $errors[] = RuleErrorBuilder::message(sprintf('Missing "requestBody" property for method "%s" with FormRequest parameter type "%s"', $reflection->getName(), $parameter->getName()))
                    ->identifier(RuleIdentifier::identifier('missingRequestBodyOnFormRequestMethod'))
                    ->build();
            }
        }

        return $errors;
    }
}
