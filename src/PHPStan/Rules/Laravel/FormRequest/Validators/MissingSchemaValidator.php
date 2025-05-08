<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Validators;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\ValidatorInterface;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class MissingSchemaValidator implements ValidatorInterface
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
            RuleErrorBuilder::message(sprintf('Missing schema attribute on FormRequest "%s" class', $arrayReturn->getClass()))
                ->identifier(RuleIdentifier::identifier('missingRequestSchemaAttribute'))
                ->file($arrayReturn->getFile())
                ->line($arrayReturn->getLine())
                ->build()
        ];
    }
}
