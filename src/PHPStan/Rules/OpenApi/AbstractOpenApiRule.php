<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi;

use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface as OperationValidatorInterface;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\ValidatorInterface as SchemaValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;

abstract class AbstractOpenApiRule
{
    abstract public function getValidatorTag(): string;

    public function __construct(
        protected readonly ReflectionProvider $reflectionProvider,
        protected readonly Container $container
    ) {
    }

    /**
     * @param list<ReflectionAttribute> $attributes
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    protected function validateAttributes(array $attributes): array
    {
        $errors = [];

        /**
         * @var array<SchemaValidatorInterface|OperationValidatorInterface> $validators
         */
        $validators = $this->container->getServicesByTag(sprintf('openApiTools.validators.%s', $this->getValidatorTag()));
        foreach ($attributes as $attribute) {
            $schemaInstance = $attribute->newInstance();
            foreach ($validators as $validator) {
                $errors = [
                    ...$errors,
                    ...$validator->validate($schemaInstance),
                ];
            }
        }

        return $errors;
    }
}
