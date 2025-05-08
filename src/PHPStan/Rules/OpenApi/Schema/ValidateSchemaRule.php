<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Schema;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\Helpers\Attributes;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;

readonly class ValidateSchemaRule implements Rule
{
    public function __construct(
        protected ReflectionProvider $reflectionProvider,
        protected Container          $container
    ) {
    }

    public function getNodeType(): string
    {
        return Stmt\Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Stmt\Class_) {
            return [];
        }

        $className = (string) $node->namespacedName;

        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $classAttributes = Attributes::getAttributes($reflectionClass, Schema::class);

        return $this->validateAttributes($node, $classAttributes);
    }

    /**
     * @param array<ReflectionAttribute> $attributes
     * @return array<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    protected function validateAttributes(Stmt\Class_ $node, array $attributes): array
    {
        $errors = [];

        /**
         * @var array<ValidatorInterface> $validators
         */
        $validators = $this->container->getServicesByTag('openApiTools.validators.openapi.schema');
        foreach ($attributes as $attribute) {
            $schemaInstance = $attribute->newInstance();
            foreach ($validators as $validator) {
                $errors = [
                    ...$errors,
                    ...$validator->validate($node, $schemaInstance),
                ];
            }
        }

        return $errors;
    }
}
