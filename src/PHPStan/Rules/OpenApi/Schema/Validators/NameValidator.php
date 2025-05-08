<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema\Validators;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\Generators\SchemaNameGeneratorInterface;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\ValidatorInterface;
use PhpParser\Node\Stmt;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;

readonly class NameValidator implements ValidatorInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private Container          $container,
    ) {
    }

    public function validate(Stmt\Class_ $node, Schema $schema): array
    {
        $className = (string) $node->namespacedName;

        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $classSchema = Attributes::getAttributes($reflectionClass, Schema::class)[0]?->newInstance();

        if ($classSchema === null) {
            return [];
        }

        /**
         * @var SchemaNameGeneratorInterface $generator
         */
        $generator = $this->container->getService('schemaNameGenerator');
        $preferredSchemaName = $generator->generateSchemaName($node);
        if ($classSchema->schema === $preferredSchemaName) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('Schema name "%s" does not match "%s"', $classSchema->schema, $preferredSchemaName))
                ->identifier(RuleIdentifier::identifier('schemaNameDoesNotMatchNamespace'))
                ->build()
        ];
    }
}
