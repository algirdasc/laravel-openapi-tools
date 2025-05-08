<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Rules\RuleErrorBuilder;

readonly class TagCountValidator implements ValidatorInterface
{
    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];

        $tags = is_array($operation->tags) ? $operation->tags : [];

        if (count($tags) === 0) {
            $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must have at least 1 tag', $operation->path))
                ->identifier(RuleIdentifier::identifier('operationTagCountIncorrect'))
                ->build();
        }

        return $errors;
    }
}
