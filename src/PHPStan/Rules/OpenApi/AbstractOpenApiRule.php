<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi;

use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;

abstract class AbstractOpenApiRule
{
    abstract public function getValidators(): array;

    /**
     * @var list<IdentifierRuleError>
     */
    protected array $errors = [];

    public function __construct(
        protected readonly ReflectionProvider $reflectionProvider,
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

            foreach ($this->getValidators() as $validator) {
                $validator = new $validator($this->reflectionProvider);
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
