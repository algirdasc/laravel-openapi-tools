<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Rules\RuleErrorBuilder;

readonly class ControllerMethodsValidator implements ValidatorInterface
{
    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];

        if (!$reflection instanceof ReflectionClass) {
            return [];
        }

        $methods = [];
        foreach ($reflection->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }

            $methods[] = $method->getName();
        }

        if (in_array('__invoke', $methods) && count($methods) > 1) {
            return [
                RuleErrorBuilder::message('Controller must not have any other methods if __invoke is defined')
                    ->identifier(RuleIdentifier::identifier('keepControllerCleanFromOtherMethods'))
                    ->build(),
            ];
        }

        return $errors;
    }
}
