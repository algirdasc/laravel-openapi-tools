<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use PHPStan\Rules\IdentifierRuleError;

/**
 * @deprecated
 */
interface ValidatorInterface
{
    /**
     * @return list<IdentifierRuleError>
     */
    public function validate(ReturnStatement $arrayReturn, ?Schema $schema): array;
}
