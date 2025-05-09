<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\SchemaCollector;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use OpenApiTools\PHPStan\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Helpers\SchemaProperties;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\Container;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class BooleanPropertyRule implements Rule
{
    use IteratesOverCollection;

    public function __construct(
        private Container $container,
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
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        $errors = [];

        /** @var SchemaAttribute $schemaAttribute */
        foreach ($this->getIterator($node, SchemaCollector::class) as $schemaAttribute) {
            $schema = $schemaAttribute->getSchema();

            if (Generator::isDefault($schema->properties) || empty($schema->properties)) {
                return [];
            }

            /**
             * @var PropertyNameGeneratorInterface $propertyNameGenerator
             */
            $propertyNameGenerator = $this->container->getService('propertyNameGenerator');

            foreach ($schema->properties as $property) {
                if ($property->type === 'boolean' && !$propertyNameGenerator->isBooleanProperty($property->property)) {

                    $schemaNode = SchemaProperties::findFromNodeByName($schemaAttribute->getAttribute(), $property->property);

                    $errors[] = RuleErrorBuilder::message(sprintf('Schema property "%s" must start with "is" or "has"', $property->property))
                        ->identifier(RuleIdentifier::identifier('booleanInJsonResourceMustStartWithIs'))
                        ->file($schemaAttribute->getFile())
                        ->line($schemaNode?->getLine() ?? -1)
                        ->build();
                }
            }
        }

        return $errors;
    }
}
