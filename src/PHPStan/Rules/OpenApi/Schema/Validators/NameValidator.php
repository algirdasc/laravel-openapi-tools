<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema\Validators;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Generators\SchemaNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\ValidatorInterface;
use PhpParser\Node\Stmt;
use PHPStan\DependencyInjection\Container;
use PHPStan\Rules\RuleErrorBuilder;

readonly class NameValidator implements ValidatorInterface
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function validate(Stmt\Class_ $node, Schema $schema): array
    {
        /**
         * @var SchemaNameGeneratorInterface $generator
         */
        $generator = $this->container->getService('schemaNameGenerator');
        $preferredSchemaName = $generator->generateSchemaName($node);
        if ($schema->schema === $preferredSchemaName) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('Schema name "%s" does not match "%s"', $schema->schema, $preferredSchemaName))
                ->identifier(RuleIdentifier::identifier('schemaNameDoesNotMatchNamespace'))
                ->build()
        ];
    }
}
