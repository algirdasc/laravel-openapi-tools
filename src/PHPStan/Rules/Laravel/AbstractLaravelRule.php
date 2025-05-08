<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\ValidatorInterface as FormRequestValidatorInterface;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface as ResourceValidatorInterface;;

use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Collectors\Collector;
use PHPStan\DependencyInjection\Container;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;

abstract class AbstractLaravelRule
{
    /**
     * @var array<class-string>
     */
    private array $validationCache = [];

    abstract public function getValidatorTag(): string;

    /**
     * @return class-string<Collector>
     */
    abstract public function getCollector(): string;

    public function __construct(
        protected readonly ReflectionProvider $reflectionProvider,
        protected readonly Container $container,
    ) {
    }

    /**
     * @return array<IdentifierRuleError>
     */
    protected function validateCollector(CollectedDataNode $node): array
    {
        $errors = [];

        /**
         * @var array<FormRequestValidatorInterface|ResourceValidatorInterface> $validators
         */
        $validators = $this->container->getServicesByTag(sprintf('openApiTools.validators.%s', $this->getValidatorTag()));
        $collectedData = $node->get($this->getCollector());
        foreach ($collectedData as $declarations) {
            foreach ($declarations as $declaration) {
                /** @var ArrayReturn $arrayReturn */
                $arrayReturn = unserialize($declaration);
                if ($arrayReturn->isParentScoped()) {
                    continue;
                }

                /**
                 * @var ReflectionClass $reflection
                 */
                $reflection = $this->reflectionProvider->getClass($arrayReturn->getClass())->getNativeReflection();
                $schema = Attributes::getAttributes($reflection, Schema::class)[0] ?? null;
                /** @var Schema|null $schemaInstance */
                $schemaInstance = $schema?->newInstance();

                foreach ($validators as $validator) {
                    $errors = [
                        ...$errors,
                        ...$validator->validate($arrayReturn, $schemaInstance)
                    ];
                }
            }
        }

        return $errors;
    }
}
