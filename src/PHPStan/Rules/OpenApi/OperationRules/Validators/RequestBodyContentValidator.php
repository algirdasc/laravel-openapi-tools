<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\ValidatorInterface;

class RequestBodyContentValidator implements ValidatorInterface
{
    public function validate(Operation $operation): array
    {
        $errors = [];

        $a = 0;

        return $errors;
    }
}
