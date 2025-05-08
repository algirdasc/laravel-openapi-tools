<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Rules\RuleErrorBuilder;

readonly class DescriptionValidator implements ValidatorInterface
{
    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];

        $description = !Generator::isDefault($operation->description) ? $operation->description : '';

        if (strlen($description) < 20) {
            $errors[] = RuleErrorBuilder::message(
                sprintf('Path "%s" description is too short, must be at least 20 chars', $operation->path)
            )
                ->identifier(RuleIdentifier::identifier('operationDescriptionTooShort'))
                ->build();
        }

        return $errors;
    }
}
