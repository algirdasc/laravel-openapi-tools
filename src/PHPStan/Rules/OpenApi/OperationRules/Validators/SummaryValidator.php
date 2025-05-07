<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators;

use OpenApi\Annotations\Operation;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\ValidatorInterface;
use PHPStan\Rules\RuleErrorBuilder;

class SummaryValidator implements ValidatorInterface
{
    public function validate(Operation $operation): array
    {
        $errors = [];
        $summary = !Generator::isDefault($operation->summary) ? $operation->summary : '';

        if (strlen($summary) < 10) {
            $errors[] = RuleErrorBuilder::message(
                sprintf('Path "%s %s" summary is too short, must be at least 10 chars', $operation::class, $operation->path)
            )
                ->identifier(RuleIdentifier::identifier('operationSummaryTooShort'))
                ->build();
        }

        if (strlen($summary) > 64) {
            $errors[] = RuleErrorBuilder::message(
                sprintf('Path "%s %s" summary is too long, must be up to 64 chars', $operation::class, $operation->path)
            )
                ->identifier(RuleIdentifier::identifier('operationSummaryTooLong'))
                ->build();
        }

        return $errors;
    }
}
