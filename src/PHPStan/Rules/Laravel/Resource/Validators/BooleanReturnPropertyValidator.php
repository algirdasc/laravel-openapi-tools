<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource\Validators;

use Illuminate\Support\Str;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class BooleanReturnPropertyValidator implements ValidatorInterface
{
    /**
     * @throws ShouldNotHappenException
     */
    public function validate(ArrayReturn $arrayReturn, ?Schema $schema): array
    {
        if (Generator::isDefault($schema?->properties) || empty($schema->properties)) {
            return [];
        }

        $errors = [];

        foreach ($schema->properties as $property) {
            if ($property->type === 'boolean' && !str_starts_with($property->property, 'is_')) {
                $errors[] = RuleErrorBuilder::message(sprintf('Schema property "%s" is not returned in JsonResource "%s" class', $property->property, $arrayReturn->getClass()))
                    ->identifier(RuleIdentifier::identifier('booleanInJsonResourceMustStartWithIs'))
                    ->file($arrayReturn->getFile())
                    ->line($arrayReturn->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
