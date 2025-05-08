<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Attributes\Schema;
use PHPStan\Rules\IdentifierRuleError;
use PhpParser\Node\Stmt;
use PHPStan\ShouldNotHappenException;

interface ValidatorInterface
{
    /**
     * @return array<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    public function validate(Stmt\Class_ $node, Schema $schema): array;
}
