<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Annotations\Property;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Abstract\RecursivePropertiesRule;
use PhpParser\Node;
use PHPStan\DependencyInjection\Container;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<CollectedDataNode>
 */
class PropertiesRule extends RecursivePropertiesRule implements Rule
{
    private const array PROPERTY_TYPES = [
        'null', 'boolean', 'object', 'array', 'number', 'string', 'integer',
    ];

    private const array PROPERTY_FORMATS = [
        'int32', 'int64', 'float', 'double', 'password', 'date-time',
    ];

    private PropertyNameGeneratorInterface $propertyNameGenerator;

    public function __construct(
        private readonly Container $container,
    ) {
        $this->propertyNameGenerator = $this->container->getService('propertyNameGenerator');
    }

    public function validateProperty(Property $property, Node\ArrayItem $node): array
    {
        $errors = [];

        if (!Generator::isDefault($property->type) && !in_array($property->type, self::PROPERTY_TYPES)) {
            $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect type', $property->property))
                ->identifier(RuleIdentifier::identifier('schemaPropertyTypeIncorrect'))
                ->file($this->file)
                ->line($node->getLine())
                ->build();
        }

        if (!Generator::isDefault($property->format) && !in_array($property->format, self::PROPERTY_FORMATS)) {
            $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect format', $property->property))
                ->identifier(RuleIdentifier::identifier('schemaPropertyFormatIncorrect'))
                ->file($this->file)
                ->line($node->getLine())
                ->build();
        }

        $generatedPropertyName = $this->propertyNameGenerator->generatePropertyName($property->property);
        if ($property->property !== $generatedPropertyName) {
            $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect case, expected "%s"', $property->property, $generatedPropertyName))
                ->identifier(RuleIdentifier::identifier('schemaPropertyNameCaseIncorrect'))
                ->file($this->file)
                ->line($node->getLine())
                ->build();
        }

        $isDateProperty = $this->propertyNameGenerator->isDateProperty($property->property);
        if ($isDateProperty && $property->type !== 'string') {
            $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has must have "string" type', $property->property))
                ->identifier(RuleIdentifier::identifier('schemaDatePropertyTypeMissing'))
                ->file($this->file)
                ->line($node->getLine())
                ->build();
        }

        if ($isDateProperty && $property->format !== 'date-time') {
            $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has must have "date-time" format', $property->property))
                ->identifier(RuleIdentifier::identifier('schemaDatePropertyFormatMissing'))
                ->file($this->file)
                ->line($node->getLine())
                ->build();
        }

        return $errors;
    }
}
