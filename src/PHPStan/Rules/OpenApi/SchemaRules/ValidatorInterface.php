<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\SchemaRules;

use OpenApi\Attributes\Schema;
use PHPStan\Rules\IdentifierRuleError;

interface ValidatorInterface
{
    /**
     * @return array<IdentifierRuleError>
     */
    public function validate(Schema $schema): array;
}
