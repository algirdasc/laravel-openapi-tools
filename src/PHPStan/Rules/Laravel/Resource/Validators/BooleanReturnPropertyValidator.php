<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource\Validators;

use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface;
use PHPStan\DependencyInjection\Container;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class BooleanReturnPropertyValidator implements ValidatorInterface
{
    public function __construct(
        private Container $container,
    ) {
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function validate(ArrayReturn $arrayReturn, ?Schema $schema): array
    {
        if (Generator::isDefault($schema?->properties) || empty($schema->properties)) {
            return [];
        }

        $errors = [];

        /**
         * @var PropertyNameGeneratorInterface $propertyNameGenerator
         */
        $propertyNameGenerator = $this->container->getService('propertyNameGenerator');

        foreach ($schema->properties as $property) {
            if ($property->type === 'boolean' && !$propertyNameGenerator->isBooleanProperty($property->property)) {
                $errors[] = RuleErrorBuilder::message(sprintf('Schema property "%s" must start with "is" or "has"', $property->property))
                    ->identifier(RuleIdentifier::identifier('booleanInJsonResourceMustStartWithIs'))
                    ->file($arrayReturn->getFile())
                    ->line($arrayReturn->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
