<?php

declare(strict_types=1);

namespace OpenApiTools\Rules\OpenApi\OperationRules\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\Rules\OpenApi\OperationRules\ValidatorInterface;

class PathValidator implements ValidatorInterface
{
    public function validate(Operation $operation): array
    {
        $errors = [];

        $path = $operation->path;
        $parameters = is_array($operation->parameters) ? $operation->parameters : [];

        if (count($parameters) < substr_count($path, '{')) {
            $errors[] = RuleErrorBuilder::message(sprintf('Documentation for "%s" missing parameters definitions', $path))
                ->identifier('openApiAttribute.pip')
                ->build();
        }

        return $errors;
    }
}
