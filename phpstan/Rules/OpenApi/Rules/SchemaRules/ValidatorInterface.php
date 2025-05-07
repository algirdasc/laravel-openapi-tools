<?php

declare(strict_types=1);

namespace Rules\Rules\SchemaRules;

use OpenApi\Attributes\Schema;
use Rules\Rules\OperationRules\IdentifierRuleError;

interface ValidatorInterface
{
    /**
     * @return array<IdentifierRuleError>
     */
    public function validate(Schema $schema): array;
}
