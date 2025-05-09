<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Node\Stmt\Class_>
 */
readonly class SchemaNameRule implements Rule
{
    public function __construct(
        protected ReflectionProvider $reflectionProvider,
        protected Container          $container
    ) {
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return [];
        }

        $className = (string) $node->namespacedName;

        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        /** @var Schema|null $schema */
        $schema = Attributes::getAttributes($reflectionClass, Schema::class)[0]?->newInstance() ?? null;

        if ($schema === null) {
            return [];
        }

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
