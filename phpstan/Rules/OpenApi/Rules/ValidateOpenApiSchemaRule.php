<?php

declare(strict_types=1);

namespace Rules\Rules;

use OpenApi\Attributes as OA;
use OpenApi\Generator;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * This rule validates schema names and properties in OpenAPI attributes.
 *
 * @implements Rule<Stmt\Class_>
 */
readonly class ValidateOpenApiSchemaRule implements Rule
{
    private const array PROPERTY_TYPES = [
        'null', 'boolean', 'object', 'array', 'number', 'string', 'integer',
    ];

    private const array PROPERTY_FORMATS = [
        'int32', 'int64', 'float', 'double', 'password', 'date-time',
    ];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
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
        $errors = [];

        $classReflection = $this->reflectionProvider->getClass((string) $node->namespacedName)->getNativeReflection();
        $attributes = $classReflection->getAttributes(OA\Schema::class);
        foreach ($attributes as $attribute) {
            $plainNamespace = str_replace('App\\Http\\', '', (string) $node->namespacedName);
            $generatedSchemaName = str_replace('\\', '.', $plainNamespace);

            $arguments = $attribute->getArguments();
            $schemaName = $arguments['schema'] ?? '';
            if ($generatedSchemaName !== $schemaName) {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'Schema name "%s" does not match "%s"',
                        $schemaName,
                        $generatedSchemaName
                    )
                )
                    ->identifier('openApi.schemaNameDoesNotMatchNamespace')
                    ->build();
            }

            $errors = [
                ...$errors,
                ...$this->validateProperties($arguments),
            ];
        }

        return $errors;
    }

    /**
     * @param array<array-key, mixed> $arguments
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    private function validateProperties(array $arguments): array
    {
        $errors = [];
        $properties = [];

        if (!is_array($arguments['properties'] ?? null)) {
            return [];
        }

        foreach ($arguments['properties'] as $property) {
            $properties[] = $property->property;

            if (!Generator::isDefault($property->type) && !in_array($property->type, self::PROPERTY_TYPES)) {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect type', $property->property))
                    ->identifier('openApi.propertyTypeIncorrect')
                    ->build();
            }

            if (!Generator::isDefault($property->format) && !in_array($property->format, self::PROPERTY_FORMATS)) {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" has incorrect format', $property->property))
                    ->identifier('openApi.propertyFormatIncorrect')
                    ->build();
            }

            // TODO: check for snake case

            if ($property->items instanceof OA\Items) {
                $errors = [
                    ...$errors,
                    ...$this->validateProperties((array) $property->items),
                ];
            }
        }

        if (empty($arguments['required'])) {
            return $errors;
        }

        if (Generator::isDefault($arguments['required'])) {
            return $errors;
        }

        foreach ($arguments['required'] as $requirement) {
            if (in_array($requirement, $properties, true)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Required property "%s" is not defined in properties', $requirement))
                ->identifier('openApi.requiredPropertyNotDefined')
                ->build();
        }

        return $errors;
    }
}
