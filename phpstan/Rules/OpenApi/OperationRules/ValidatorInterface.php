<?php

declare(strict_types=1);

namespace OpenApiTools\Rules\OpenApi\OperationRules;

use OpenApi\Annotations\Operation;

interface ValidatorInterface
{
    /**
     * @return array<IdentifierRuleError>
     */
    public function validate(Operation $operation): array;
}
