<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class PathValidator implements ValidatorInterface
{
    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];

        $path = $operation->path;

        if (!str_starts_with($operation->path, '/')) {
            $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must start with /', $path))
                ->identifier(RuleIdentifier::identifier('operationPathDoesNotStartWithSlash'))
                ->build();
        }

        if (str_ends_with($operation->path, '/')) {
            $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must not end with trailing slash', $path))
                ->identifier(RuleIdentifier::identifier('operationPathMustNotEndWithSlash'))
                ->build();
        }

        return $errors;
    }
}
