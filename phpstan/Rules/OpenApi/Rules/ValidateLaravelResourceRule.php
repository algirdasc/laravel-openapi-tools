<?php

declare(strict_types=1);

namespace Rules\Rules;

use OpenApi\Attributes\Schema;
use PhpParser\Node\ArrayItem;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use Rules\Collectors\JsonResourceReturnArrayCollector;

/**
 * @implements Rule<CollectedDataNode>
 */
class ValidateLaravelResourceRule extends AbstractLaravelRule implements Rule
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
        return JsonResourceReturnArrayCollector::class;
    }

    /**
     * @return array<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    protected function validate(array $declaration): array
    {
        $errors = [];

        $class = $declaration['class'];
        $classLine = $declaration['line'];
        /** @var array<ArrayItem> $returnItems */
        $returnItems = $declaration['items'];

        $reflectionClass = $this->reflectionProvider->getClass($class)->getNativeReflection();
        $schemaAttribute = $reflectionClass->getAttributes(Schema::class)[0] ?? null;

        if (!$schemaAttribute) {
            if ($this->alreadyValidated($class, 'schemaAttribute')) {
                return [];
            }

            return [
                RuleErrorBuilder::message(sprintf('Missing OpenApi schema on JsonResource "%s" class', $class))
                    ->file($reflectionClass->getFileName())
                    ->line($classLine)
                    ->identifier('openApi.missingResourceSchemaAttribute')
                    ->build()
            ];
        }

        $schema = $schemaAttribute->getArguments();
        if (empty($schema['properties'])) {
            if ($this->alreadyValidated($class, 'schemaProperties')) {
                return [];
            }

            return [
                RuleErrorBuilder::message(sprintf('Missing OpenApi schema properties on JsonResource "%s" class', $class))
                    ->file($reflectionClass->getFileName())
                    ->line($classLine)
                    ->identifier('openApi.missingResourceSchemaProperties')
                    ->build()
            ];
        }

        // skip check if the return item is empty, probably null check return
        if (empty($returnItems)) {
            return [];
        }

        $schemaProperties = [];
        $returnedProperties = [];
        foreach ($schema['properties'] as $property) {
            $schemaProperties[$property->property] = 0;
        }

        foreach ($returnItems as $returnItem) {
            if ($returnItem->unpack === true) {
                continue;
            }

            /** @phpstan-ignore-next-line */
            $key = $returnItem->key->value;
            $returnedProperties[$key] = $returnItem->key->getLine();
        }

        $schemaDiff = array_diff_key($returnedProperties, $schemaProperties);
        foreach ($schemaDiff as $property => $line) {
            $errors[] = RuleErrorBuilder::message(sprintf('Returned property "%s" is not defined in OpenApi schema "%s"', $property, $class)) // @phpcs:ignore
                ->identifier('openApi.missingResourceReturnedProperty')
                ->file($reflectionClass->getFileName())
                ->line($line)
                ->build();
        }

        $returnDiff = array_diff_key($schemaProperties, $returnedProperties);
        foreach ($returnDiff as $property => $line) {
            $errors[] = RuleErrorBuilder::message(sprintf('Schema property "%s" is not returned in JsonResource "%s"', $property, $class)) // @phpcs:ignore
                ->identifier('openApi.missingResourceSchemaroperty')
                ->file($reflectionClass->getFileName())
                ->line($line)
                ->build();
        }

        return $errors;
    }
}
