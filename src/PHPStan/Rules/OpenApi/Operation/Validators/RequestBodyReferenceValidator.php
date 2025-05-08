<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;

readonly class RequestBodyReferenceValidator implements ValidatorInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];
        $requestBody = !Generator::isDefault($operation->requestBody) ? $operation->requestBody : null;

        if ($requestBody === null) {
            return $errors;
        }

        $contentReference = Generator::isDefault($requestBody->content)
            ? ($requestBody->_unmerged[0]->ref ?? null)
            : ($requestBody->content->ref ?? null);

        if ($contentReference === null || Generator::isDefault($contentReference)) {
            return $errors;
        }

        /**
         * @var ReflectionClass $reflection
         */
        $reflection = $this->reflectionProvider->getClass($contentReference)->getNativeReflection();
        $schema = Attributes::getAttributes($reflection, Schema::class);

        if (empty($schema)) {
            $errors[] = RuleErrorBuilder::message(sprintf('RequestBody reference "%s" does not have schema attribute', $contentReference))
                ->identifier(RuleIdentifier::identifier('operationRequestBodyReferenceHasEmptySchema'))
                ->build();
        }

        return $errors;
    }
}
