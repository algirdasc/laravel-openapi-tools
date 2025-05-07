<?php

declare(strict_types=1);

namespace Rules\Rules;

use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Rules\Collectors\FormRequestRuleArrayCollector;

/**
 * @implements Rule<CollectedDataNode>
 */
class ValidateLaravelRequestRule extends AbstractLaravelRule implements Rule
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    protected function getCollector(): string
    {
        return FormRequestRuleArrayCollector::class;
    }

    protected function validate(array $declaration): array
    {
        $errors = [];

        $class = $declaration['class'];
        $classLine = $declaration['line'];
        /** @var array<ArrayItem> $ruleItems */
        $ruleItems = $declaration['items'];

        $reflectionClass = $this->reflectionProvider->getClass($class)->getNativeReflection();
        $schemaAttribute = $reflectionClass->getAttributes(Schema::class)[0] ?? null;

        if (!$schemaAttribute) {
            if ($this->alreadyValidated($class, 'schemaAttribute')) {
                return [];
            }

            return [
                RuleErrorBuilder::message(sprintf('Missing OpenApi schema on FormRequest "%s" class', $class))
                    ->file($reflectionClass->getFileName())
                    ->line($classLine)
                    ->identifier('openApi.missingRequestSchemaAttribute')
                    ->build()
            ];
        }

        $schema = $schemaAttribute->getArguments();
        if (empty($schema['properties'])) {
            if ($this->alreadyValidated($class, 'schemaProperties')) {
                return [];
            }

            return [
                RuleErrorBuilder::message(sprintf('Missing OpenApi schema properties on FormRequest "%s" class', $class))
                    ->file($reflectionClass->getFileName())
                    ->line($classLine)
                    ->identifier('openApi.missingRequestSchemaProperties')
                    ->build()
            ];
        }

        // skip check if the return item is empty, probably null check return
        if (empty($ruleItems)) {
            return [];
        }

        $ruleProperties = [];
        $requiredProperties = [];
        $nullableProperties = [];
        $schemaRequired = $schema['required'] ?? [];
        foreach ($ruleItems as $ruleItem) {
            if ($ruleItem->unpack === true) {
                continue;
            }

            /** @phpstan-ignore-next-line */
            $ruleProperty = $ruleItem->key->value;
            if (str_contains($ruleProperty, '.')) {
                // support for nested properties will be later
                continue;
            }

            $ruleProperties[] = $ruleProperty;

            if ($ruleItem->value instanceof Node\Expr\Array_) {
                foreach ($ruleItem->value->items as $item) {
                    if ($item->value instanceof Node\Scalar\String_ && $item->value->value === 'required') {
                        $requiredProperties[$ruleProperty] = $ruleItem->value->getLine();
                    }
                    if ($item->value instanceof Node\Scalar\String_ && $item->value->value === 'nullable') {
                        $nullableProperties[$ruleProperty] = $ruleItem->value->getLine();
                    }
                }
            } elseif ($ruleItem->value instanceof Node\Scalar\String_) {
                if (str_contains($ruleItem->value->value, 'required')) {
                    $requiredProperties[$ruleProperty] = $ruleItem->value->getLine();
                }
                if (str_contains($ruleItem->value->value, 'nullable')) {
                    $nullableProperties[$ruleProperty] = $ruleItem->value->getLine();
                }
            }
        }

        $schemaProperties = $this->getSchemaProperties($schema);

        $propertiesDiff = array_diff($ruleProperties, array_keys($schemaProperties));
        foreach ($propertiesDiff as $property) {
            $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" is in rules, but not in schema', $property)) // @phpcs:ignore
            ->identifier('openApi.missingRequestSchemaProperty')
                ->file($reflectionClass->getFileName())
                ->line($classLine)
                ->build();
        }

        $rulesDiff = array_diff_key($requiredProperties, array_flip($schemaRequired));
        foreach ($rulesDiff as $property => $line) {
            $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" is required in rules, but not in schema', $property)) // @phpcs:ignore
            ->identifier('openApi.missingRequestRequiredRuleInSchema')
                ->file($reflectionClass->getFileName())
                ->line($line)
                ->build();
        }

        foreach ($nullableProperties as $nullableProperty => $line) {
            $property = $schemaProperties[$nullableProperty] ?? null;
            if ($property === null) {
                continue;
            }

            if (!$property['nullable']) {
                $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" is nullable in rules, but not in schema', $nullableProperty)) // @phpcs:ignore
                ->identifier('openApi.missingRequestNullableRuleInSchema')
                    ->file($reflectionClass->getFileName())
                    ->line($line)
                    ->build();
            }
        }

        return $errors;
    }

    private function getSchemaProperties(array $schema): array
    {
        $properties = [];
        $schemaProperties = Generator::isDefault($schema['properties']) ? [] : $schema['properties'];
        foreach ($schemaProperties as $property) {
            $properties[$property->property] = [
                'nullable' => Generator::isDefault($property->nullable) ? false : $property->nullable,
            ];
        }

        return $properties;
    }
}
