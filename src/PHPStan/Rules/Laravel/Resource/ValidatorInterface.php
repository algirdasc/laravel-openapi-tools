<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use PHPStan\Rules\IdentifierRuleError;

interface ValidatorInterface
{
    /**
     * @return list<IdentifierRuleError>
     */
    public function validate(ArrayReturn $arrayReturn, ?Schema $schema): array;
}
