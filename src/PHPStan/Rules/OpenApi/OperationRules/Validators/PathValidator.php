<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\ValidatorInterface;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class PathValidator implements ValidatorInterface
{
    public function validate(Operation $operation): array
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
