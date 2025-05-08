<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema\Validators;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\ValidatorInterface;
use PhpParser\Node\Stmt;
use PHPStan\DependencyInjection\Container;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class PropertiesValidator implements ValidatorInterface
{
    private const array PROPERTY_TYPES = [
        'null', 'boolean', 'object', 'array', 'number', 'string', 'integer',
    ];

    private const array PROPERTY_FORMATS = [
        'int32', 'int64', 'float', 'double', 'password', 'date-time',
    ];

    public function __construct(
        private Container $container,
    ) {
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function validate(Stmt\Class_ $node, Schema $schema): array
    {
        if (!Generator::isDefault($schema->type) && $schema->type !== 'object') {
            return [];
        }

        return $this->validateRecursive($schema);
    }

    /**
     * @return array<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    private function validateRecursive(Schema|Items $schema): array
    {
        $errors = [];
        $propertyNames = [];

        $properties = !Generator::isDefault($schema->properties) ? $schema->properties : [];

        /**
         * @var PropertyNameGeneratorInterface $propertyNameGenerator
         */
        $propertyNameGenerator = $this->container->getService('propertyNameGenerator');

        foreach ($properties as $property) {
            $propertyNames[] = $property->property;

            if (!Generator::isDefault($property->type) && !in_array($property->type, self::PROPERTY_TYPES)) {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect type', $property->property))
                    ->identifier(RuleIdentifier::identifier('schemaPropertyTypeIncorrect'))
                    ->build();
            }

            if (!Generator::isDefault($property->format) && !in_array($property->format, self::PROPERTY_FORMATS)) {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect format', $property->property))
                    ->identifier(RuleIdentifier::identifier('schemaPropertyFormatIncorrect'))
                    ->build();
            }

            $generatedPropertyName = $propertyNameGenerator->generatePropertyName($property->property);
            if ($property->property !== $generatedPropertyName) {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect case, expected "%s"', $property->property, $generatedPropertyName))
                    ->identifier(RuleIdentifier::identifier('schemaPropertyNameCaseIncorrect'))
                    ->build();
            }

            $isDateProperty = $propertyNameGenerator->isDateProperty($property->property);
            if ($isDateProperty && $property->type !== 'string') {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has must have "string" type', $property->property))
                    ->identifier(RuleIdentifier::identifier('schemaDatePropertyTypeMissing'))
                    ->build();
            }

            if ($isDateProperty && $property->format !== 'date-time') {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has must have "date-time" format', $property->property))
                    ->identifier(RuleIdentifier::identifier('schemaDatePropertyFormatMissing'))
                    ->build();
            }

            if ($property->items instanceof Items) {
                $errors = [
                    ...$errors,
                    ...$this->validateRecursive($property->items),
                ];
            }
        }

        if (Generator::isDefault($schema->required)) {
            return $errors;
        }

        foreach ($schema->required as $requirement) {
            if (in_array($requirement, $propertyNames, true)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Required property "%s" is not defined in properties', $requirement))
                ->identifier(RuleIdentifier::identifier('schemaRequiredPropertyNotDefined'))
                ->build();
        }

        return $errors;
    }
}
