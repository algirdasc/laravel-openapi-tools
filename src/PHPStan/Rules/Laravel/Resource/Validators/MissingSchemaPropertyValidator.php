<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource\Validators;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Helpers\SchemaProperties;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Generators\RuleGenerator;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface;
use PhpParser\Node;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class MissingSchemaPropertyValidator implements ValidatorInterface
{
    /**
     * @throws ShouldNotHappenException
     */
    public function validate(ArrayReturn $arrayReturn, ?Schema $schema): array
    {
        if ($schema === null) {
            return [];
        }

        $errors = [];

        /**
         * @var string $property
         * @var Node\ArrayItem $item
         */
        foreach (RuleGenerator::iterate($arrayReturn) as [$property, $item]) {
            $schemaProperty = SchemaProperties::findByName($schema, $property);
            if ($schemaProperty === null) {
                RuleErrorBuilder::message(sprintf('Returned property "%s" is not defined in schema "%s" class', $property, $arrayReturn->getClass()))
                ->identifier(RuleIdentifier::identifier('missingJsonResourceSchemaProperty'))
                    ->file($arrayReturn->getFile())
                    ->line($item->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
