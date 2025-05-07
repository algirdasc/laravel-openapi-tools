<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi;

use OpenApiTools\PHPStan\Rules\OpenApi\OperationRules\ValidatorInterface as OperationValidatorInterface;
use OpenApiTools\PHPStan\Rules\OpenApi\SchemaRules\ValidatorInterface as SchemaValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;

abstract class AbstractOpenApiRule
{
    abstract protected function getValidatorTag(): string;

    /**
     * @var list<IdentifierRuleError>
     */
    protected array $errors = [];

    public function __construct(
        protected readonly ReflectionProvider $reflectionProvider,
        protected readonly Container $container
    ) {
    }

    /**
     * @param list<ReflectionAttribute> $attributes
     * @throws ShouldNotHappenException
     */
    protected function validateAttributes(array $attributes): void
    {
        $tagName = sprintf('openApiTools.validators.%s', $this->getValidatorTag());

        /**
         * @var array<SchemaValidatorInterface|OperationValidatorInterface> $validators
         */
        $validators = $this->container->getServicesByTag($tagName);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            foreach ($validators as $validator) {
                $this->addErrors($validator->validate($instance));
            }
        }
    }

    /**
     * @param list<IdentifierRuleError> $errors
     */
    protected function addErrors(array $errors): void
    {
        $this->errors = [
            ...$this->errors,
            ...$errors
        ];
    }
}
