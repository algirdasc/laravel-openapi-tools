<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\ValidatorInterface;

class TagCountValidator implements ValidatorInterface
{
    public function validate(Operation $operation): array
    {
        $errors = [];

        $tags = is_array($operation->tags) ? $operation->tags : [];

        if (count($tags) === 0) {
            $errors[] = RuleErrorBuilder::message(sprintf('Documentation for "%s" must have at least 1 tag', $tags))
                ->identifier('openApiAttribute.incorrectTagCount')
                ->build();
        }

        return $errors;
    }
}
