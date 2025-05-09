<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Collectors\FormRequestRuleReturnCollector;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\DependencyInjection\Container;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
class MissingSchemaRule implements Rule
{
    use IteratesOverCollection;

    public function __construct(
        protected readonly ReflectionProvider $reflectionProvider,
        protected readonly Container $container,
    ) {
    }

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        /** @var ReturnStatement $returnStatement */
        foreach ($this->getIterator($node, [FormRequestRuleReturnCollector::class]) as $returnStatement) {
            $className = $returnStatement->getClass();

            /**
             * @var ReflectionClass $reflection
             */
            $reflection = $this->reflectionProvider->getClass($className)->getNativeReflection();
            $schema = Attributes::getAttributes($reflection, Schema::class)[0] ?? null;

            if ($schema === null) {
                $errors[$className] = RuleErrorBuilder::message(sprintf('Missing schema attribute on FormRequest "%s" class', $className))
                    ->identifier(RuleIdentifier::identifier('missingRequestSchemaAttribute'))
                    ->file($returnStatement->getFile())
                    ->line($returnStatement->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
