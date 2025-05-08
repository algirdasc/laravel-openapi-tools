<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Rules\RuleErrorBuilder;

readonly class ControllerInvokeMethodValidator implements ValidatorInterface
{
    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        if (!$reflection instanceof ReflectionMethod || $reflection->getName() !== '__invoke') {
            return [];
        }

        return [
            RuleErrorBuilder::message('OpenApi attributes must be applied on class scope')
                ->identifier(RuleIdentifier::identifier('useClassScopeAttributes'))
                ->build(),
        ];
    }
}
