<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Annotations\Property;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
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
readonly class RequiredPropertiesRule implements Rule
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
        $className = (string)$node->namespacedName;

        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        /** @var Schema|null $schema */
        $schema = Attributes::getAttributes($reflectionClass, Schema::class)[0]?->newInstance() ?? null;

        if ($schema === null) {
            return [];
        }

        if (!Generator::isDefault($schema->type) && $schema->type !== 'object') {
            return [];
        }

        $undefinedProperties = $this->validateRecursive($schema);
        if (!$undefinedProperties) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('Required properties "%s" is not defined in properties', implode('", "', $undefinedProperties)))
                ->identifier(RuleIdentifier::identifier('schemaRequiredPropertiesNotDefined'))
                ->build()
        ];
    }

    /**
     * @return array<string>
     * @throws ShouldNotHappenException
     */
    private function validateRecursive(Schema|Items|Property $schema): array
    {
        $undefinedProperties = [];
        $propertyNames = [];
        $properties = !Generator::isDefault($schema->properties) ? $schema->properties : [];
        foreach ($properties as $property) {
            $propertyNames[] = $property->property;

            if ($property->items instanceof Items) {
                $undefinedProperties = [
                    ...$undefinedProperties,
                    ...$this->validateRecursive($property->items),
                ];
            }

            if (!Generator::isDefault($property->properties) ? $property->properties : []) {
                $undefinedProperties = [
                    ...$undefinedProperties,
                    ...$this->validateRecursive($property),
                ];
            }
        }

        if (Generator::isDefault($schema->required)) {
            return $undefinedProperties;
        }

        foreach ($schema->required as $requirement) {
            if (in_array($requirement, $propertyNames, true)) {
                continue;
            }

            $undefinedProperties[] = $requirement;
        }

        return $undefinedProperties;
    }
}