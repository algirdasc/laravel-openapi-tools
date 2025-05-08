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

readonly class RequestBodyBuiltinParametersValidator implements ValidatorInterface
{
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

        $orderedParameters = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
                continue;
            }

            if ($type->getName() !== 'string') {
                $errors[] = RuleErrorBuilder::message(sprintf('Method "%s" parameter "%s" must be of type string', $reflection->getName(), $parameter->getName()))
                    ->identifier(RuleIdentifier::identifier('incorrectMethodParametersType'))
                    ->build();

                continue;
            }

            $orderedParameters[] = $parameter->getName();
        }

        if (!$orderedParameters) {
            return $errors;
        }

        preg_match_all('/{(.*?)}/', $operation->path, $parameters);
        $parametersDiff = array_diff_assoc($parameters[1], $orderedParameters);
        if ($parametersDiff) {
            $errors[] = RuleErrorBuilder::message(sprintf('Method "%s" parameters "%s" are either missing or not in the correct order', $reflection->getName(), implode(', ', $parametersDiff)))
                ->identifier(RuleIdentifier::identifier('incorrectMethodParametersOrder'))
                ->build();
        }

        return $errors;
    }
}
