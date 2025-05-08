<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\SchemaName;

use PhpParser\Node\Stmt;

interface SchemaNameValidatorInterface
{
    public function getPreferredSchemaName(Stmt\Class_ $node): string;
}
