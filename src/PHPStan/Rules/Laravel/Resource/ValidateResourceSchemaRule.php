<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<Node\Stmt\Class_>
 */
class ValidateResourceSchemaRule implements Rule
{
    public function __construct(
        protected ReflectionProvider $reflectionProvider,
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
        $schema = Attributes::getAttributes($reflectionClass, Schema::class);

        if ($schema) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('Missing schema attribute on JsonResource "%s" class', $className))
                ->identifier(RuleIdentifier::identifier('missingJsonResourceSchemaAttribute'))
                ->build()
        ];
    }
}
