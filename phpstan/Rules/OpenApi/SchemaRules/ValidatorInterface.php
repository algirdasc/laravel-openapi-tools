<?php

declare(strict_types=1);

namespace OpenApiTools\Rules\OpenApi\SchemaRules;

use OpenApi\Attributes\Schema;

interface ValidatorInterface
{
    /**
     * @return array<IdentifierRuleError>
     */
    public function validate(Schema $schema): array;
}
