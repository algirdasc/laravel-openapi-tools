<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema\Validators;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\ValidatorInterface;
use PhpParser\Node\Stmt;
use PHPStan\DependencyInjection\Container;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class PropertiesValidator implements ValidatorInterface
{
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
     * @return list<IdentifierRuleError>
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
