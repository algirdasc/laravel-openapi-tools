<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\ValidatorInterface as FormRequestValidatorInterface;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\ValidatorInterface as ResourceValidatorInterface;;
use PhpParser\Node;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Collectors\Collector;
use PHPStan\DependencyInjection\Container;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;

abstract class AbstractLaravelRule
{
    abstract public function getValidatorTag(): string;

    /**
     * @return class-string<Collector<Node\Stmt\Return_, string|null>>
     */
    abstract public function getCollector(): string;

    public function __construct(
        protected readonly ReflectionProvider $reflectionProvider,
        protected readonly Container $container,
    ) {
    }

    /**
     * @return list<IdentifierRuleError>
     */
    protected function validateCollector(CollectedDataNode $node): array
    {
        $errors = [];

        /**
         * @var list<FormRequestValidatorInterface|ResourceValidatorInterface> $validators
         */
        $validators = $this->container->getServicesByTag(sprintf('openApiTools.validators.%s', $this->getValidatorTag()));
        $collectedData = $node->get($this->getCollector());
        foreach ($collectedData as $declarations) {
            foreach ($declarations as $declaration) {
                if ($declaration === null) {
                    continue;
                }

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
