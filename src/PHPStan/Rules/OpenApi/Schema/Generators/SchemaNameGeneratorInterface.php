<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema\Generators;

use PhpParser\Node\Stmt;

interface SchemaNameGeneratorInterface
{
    public function generateSchemaName(Stmt\Class_ $node): string;
}
