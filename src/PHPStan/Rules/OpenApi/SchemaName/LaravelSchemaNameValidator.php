<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\SchemaName;

use PhpParser\Node\Stmt;

class LaravelSchemaNameValidator implements SchemaNameValidatorInterface
{
    public function getPreferredSchemaName(Stmt\Class_ $node): string
    {
        return str_replace('\\', '.', str_replace('App\\Http\\', '', (string) $node->namespacedName));
    }
}
