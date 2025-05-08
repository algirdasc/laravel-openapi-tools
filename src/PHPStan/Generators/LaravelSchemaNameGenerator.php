<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Generators;

use PhpParser\Node\Stmt;

class LaravelSchemaNameGenerator implements SchemaNameGeneratorInterface
{
    public function generateSchemaName(Stmt\Class_ $node): string
    {
        return str_replace('\\', '.', str_replace('App\\Http\\', '', (string) $node->namespacedName));
    }
}
