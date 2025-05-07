<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi;

use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;

abstract class AbstractOpenApiRule
{
    abstract public function getValidators(): array;

    abstract protected function getValidatorTag(): string;

    /**
     * @var list<IdentifierRuleError>
     */
    protected array $errors = [];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly Container $container
    ) {
    }

    /**
     * @param list<ReflectionAttribute> $attributes
     * @throws ShouldNotHappenException
     */
    protected function validateAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            $validators = $this->container->getServicesByTag('openApiTools.validator.' . $this->getValidatorTag());
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
