<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Abstract;

use OpenApi\Annotations\Property;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassSchemaCollector;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use OpenApiTools\PHPStan\Helpers\NodeHelper;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
abstract class RecursivePropertiesRule implements Rule
{
    use IteratesOverCollection;

    protected string $file = '';

    /**
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    abstract public function validateProperty(Property $property, Node\ArrayItem $node): array;

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
        foreach ($this->getIterator($node, ClassSchemaCollector::class) as $schemaAttribute) {
            $this->file = $schemaAttribute->getFile();

            $schema = $schemaAttribute->getSchema();
            if (Generator::isDefault($schema->properties)) {
                continue;
            }

            /** @var Node\Expr\Array_ $nodes */
            $nodes = NodeHelper::findInArgsByName($schemaAttribute->getAttribute()->args, 'properties');

            $errors = [
                ...$errors,
                ...$this->validateProperties($nodes,  $schema->properties),
            ];
        }

        return $errors;
    }

    /**
     * @param array<Property>|string $properties
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    private function validateProperties(Node\Expr\Array_ $propertyNodes, array|string $properties): array
    {
        if (!is_array($properties)) {
            return [];
        }

        $errors = [];

        /** @var Property $property */
        foreach ($properties as $property) {
            /** @var Node\ArrayItem $propertyNode */
            $propertyNode = $this->findPropertyNodeFromArray($propertyNodes, $property->property);
            /** @var Node\Expr\New_ $propertyNodeArgs */
            $propertyNodeArgs = $propertyNode->value;

            if (!Generator::isDefault($property->properties)) {
                /** @var Node\Expr\Array_ $propertiesNode */
                $propertiesNode = NodeHelper::findInArgsByName($propertyNodeArgs->args, 'properties');

                $errors = [
                    ...$errors,
                    ...$this->validateProperties($propertiesNode, $property->properties),
                ];
            }

            if (!Generator::isDefault($property->items) && !Generator::isDefault($property->items->properties)) {
                /** @var Node\Expr\New_ $itemsNode */
                $itemsNode = NodeHelper::findInArgsByName($propertyNodeArgs->args, 'items');
                /** @var Node\Expr\Array_ $propertiesNode */
                $propertiesNode = NodeHelper::findInArgsByName($itemsNode->args, 'properties');

                $errors = [
                    ...$errors,
                    ...$this->validateProperties($propertiesNode, $property->items->properties),
                ];
            }

            $errors = [
                ...$errors,
                ...$this->validateProperty($property, $propertyNode),
            ];
        }

        return $errors;
    }

    private function findPropertyNodeFromArray(Node\Expr\Array_ $nodes, string $name): ?Node\Expr\ArrayItem
    {
        foreach ($nodes->items as $item) {
            if ($item->value->args[0]->value->value === $name) {
                return $item;
            }
        }

        return null;
    }
}
