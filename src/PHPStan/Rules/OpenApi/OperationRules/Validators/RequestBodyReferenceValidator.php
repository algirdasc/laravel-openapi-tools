<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\Validators;

use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\ValidatorInterface;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;

class RequestBodyReferenceValidator implements ValidatorInterface
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function validate(Operation $operation): array
    {
        $errors = [];
        $requestBody = !Generator::isDefault($operation->requestBody) ? $operation->requestBody : null;

        if ($requestBody === null) {
            return $errors;
        }

        $contentReference = Generator::isDefault($requestBody->content)
            ? ($requestBody->_unmerged[0]->ref ?? null)
            : ($requestBody->content->ref ?? null);

        if ($contentReference === null) {
            return $errors;
        }

        $reference = $this->reflectionProvider->getClass($contentReference)->getNativeReflection();
        $schema = Attributes::getAttributes($reference, Schema::class);

        if (empty($schema)) {
            $errors[] = RuleErrorBuilder::message(sprintf('RequestBody reference "%s" does not have schema attribute', $contentReference))
                ->identifier(RuleIdentifier::identifier('operationRequestBodyReferenceHasEmptySchema'))
                ->build();
        }

        return $errors;
    }
}
