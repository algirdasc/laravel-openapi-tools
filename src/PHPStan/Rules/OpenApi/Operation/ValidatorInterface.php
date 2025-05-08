<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Annotations\Operation;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;

interface ValidatorInterface
{
    /**
     * @return array<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    public function validate(Operation $operation): array;
}
