<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\SchemaRules\Validators;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\SchemaRules\ValidatorInterface;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class NameValidator implements ValidatorInterface
{
    /**
     * @throws ShouldNotHappenException
     */
    public function validate(Schema $schema): array
    {
        $errors = [];

        $plainNamespace = str_replace('App\\Http\\', '', (string) $node->namespacedName);
        $generatedSchemaName = str_replace('\\', '.', $plainNamespace);

        if ($generatedSchemaName !== $schema->schema) {
            $errors[] = RuleErrorBuilder::message(sprintf('Schema name "%s" does not match "%s"', $schema->schema, $generatedSchemaName))
                ->identifier(RuleIdentifier::identifier('schemaNameDoesNotMatchNamespace'))
                ->build();
        }

        return $errors;
    }
}
