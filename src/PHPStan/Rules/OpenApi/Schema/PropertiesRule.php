<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Annotations\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Abstract\RecursivePropertiesRule;
use PhpParser\Node;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Broker\ClassNotFoundException;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;

class PropertiesRule extends RecursivePropertiesRule
{
    private const array PROPERTY_TYPES = [
        'null', 'boolean', 'object', 'array', 'number', 'string', 'integer',
    ];

    private const array PROPERTY_FORMATS = [
        'int32', 'int64', 'float', 'double', 'password', 'date-time',
    ];

    private PropertyNameGeneratorInterface $propertyNameGenerator;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly Container          $container,
    ) {
        $this->propertyNameGenerator = $this->container->getService('propertyNameGenerator');
    }

    public function validateProperty(Property $property, Node\ArrayItem $node): array
    {
        $errors = [];

        // TODO: check for duplicates

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

        if (!Generator::isDefault($property->ref) && is_string($property->ref) && !str_starts_with('#/components', $property->ref)) {
            try {
                /**
                 * @var ReflectionClass $reflection
                 */
                $reflection = $this->reflectionProvider->getClass($property->ref)->getNativeReflection();
                $schema = Attributes::getAttributes($reflection, Schema::class);

                if (!$schema) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" reference "%s" does not have schema attribute', $property->property, $property->ref))
                        ->identifier(RuleIdentifier::identifier('schemaPropertyReferenceHasEmptySchema'))
                        ->file($this->file)
                        ->line($node->getLine())
                        ->build();
                }
            } catch (ClassNotFoundException $e) {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" reference "%s" does not exist', $property->property, $property->ref))
                    ->identifier(RuleIdentifier::identifier('schemaPropertyReferenceNotExist'))
                    ->file($this->file)
                    ->line($node->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
