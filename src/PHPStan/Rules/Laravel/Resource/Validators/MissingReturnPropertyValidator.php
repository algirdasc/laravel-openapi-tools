<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource\Validators;

use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface;
use OpenApiTools\PHPStan\Traits\IteratesOverReturnStatement;
use PhpParser\Node;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class MissingReturnPropertyValidator implements ValidatorInterface
{
    /**
     * @throws ShouldNotHappenException
     */
    public function validate(ReturnStatement $arrayReturn, ?Schema $schema): array
    {
        if (Generator::isDefault($schema?->properties) || empty($schema->properties) || empty($arrayReturn->getItems())) {
            return [];
        }

        $errors = [];
        $returnedProperties = $schemaProperties = [];

        /**
         * @var string $property
         * @var Node\ArrayItem $item
         */
        foreach (IteratesOverReturnStatement::iterate($arrayReturn) as [$property, $item]) {
            $returnedProperties[$property] = (int) $item->key?->getLine();
        }

        foreach ($schema->properties as $propertySchema) {
            $schemaProperties[$propertySchema->property] = 0;
        }

        $returnDiff = array_diff_key($schemaProperties, $returnedProperties);
        foreach ($returnDiff as $property => $line) {
            $errors[] = RuleErrorBuilder::message(sprintf('Schema property "%s" is not returned in JsonResource "%s" class', $property, $arrayReturn->getClass()))
                ->identifier(RuleIdentifier::identifier('missingJsonResourceReturnProperty'))
                ->file($arrayReturn->getFile())
                ->line($line)
                ->build();
        }

        return $errors;
    }
}
