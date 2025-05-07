<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\SchemaRules\Validators;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\SchemaRules\ValidatorInterface;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class PropertiesValidator implements ValidatorInterface
{
    private const array PROPERTY_TYPES = [
        'null', 'boolean', 'object', 'array', 'number', 'string', 'integer',
    ];

    private const array PROPERTY_FORMATS = [
        'int32', 'int64', 'float', 'double', 'password', 'date-time',
    ];

    /**
     * @throws ShouldNotHappenException
     */
    public function validate(Schema $schema): array
    {
        return $this->validateRecursive($schema);
    }

    /**
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    private function validateRecursive(Schema|Items $schema): array
    {
        $errors = [];
        $propertyNames = [];

        $properties = is_array($schema->properties) ? $schema->properties : [];

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

            // TODO: check for snake case

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
