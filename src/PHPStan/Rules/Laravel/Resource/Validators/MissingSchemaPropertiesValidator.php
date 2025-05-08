<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource\Validators;

use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class MissingSchemaPropertiesValidator implements ValidatorInterface
{
    /**
     * @throws ShouldNotHappenException
     */
    public function validate(ArrayReturn $arrayReturn, ?Schema $schema): array
    {
        if ($schema === null) {
            return [];
        }

        if (!Generator::isDefault($schema->type) && $schema->type !== 'object') {
            return [];
        }

        if (!Generator::isDefault($schema->properties) && !empty($schema->properties)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('Missing schema properties on JsonResource "%s" class', $arrayReturn->getClass()))
                ->identifier(RuleIdentifier::identifier('missingJsonResourceSchemaProperties'))
                ->file($arrayReturn->getFile())
                ->line($arrayReturn->getLine())
                ->build()
        ];
    }
}
