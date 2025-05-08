<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource\Validators;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

readonly class MissingSchemaValidator implements ValidatorInterface
{
    /**
     * @throws ShouldNotHappenException
     */
    public function validate(ArrayReturn $arrayReturn, ?Schema $schema): array
    {
        if ($schema !== null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('Missing schema attribute on JsonResource "%s" class', $arrayReturn->getClass()))
                ->identifier(RuleIdentifier::identifier('missingJsonResourceSchemaAttribute'))
                ->file($arrayReturn->getFile())
                ->line($arrayReturn->getLine())
                ->build()
        ];
    }
}
