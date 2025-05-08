<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Attributes\Schema;
use PHPStan\Rules\IdentifierRuleError;

interface ValidatorInterface
{
    /**
     * @return list<IdentifierRuleError>
     */
    public function validate(Schema $schema): array;
}
