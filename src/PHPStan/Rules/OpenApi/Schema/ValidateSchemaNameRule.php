<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class ValidateSchemaNameRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Stmt\Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Stmt\Class_) {
            return [];
        }

        $className = (string) $node->namespacedName;

        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $classSchema = Attributes::getAttributes($reflectionClass, Schema::class)[0]?->newInstance();

        if ($classSchema === null) {
            return [];
        }

        $preferredSchemaName = str_replace('\\', '.', str_replace('App\\Http\\', '', (string) $node->namespacedName));
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
