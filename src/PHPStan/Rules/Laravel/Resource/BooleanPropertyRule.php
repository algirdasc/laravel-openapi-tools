<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations\Property;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use OpenApiTools\PHPStan\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Abstract\RecursivePropertiesRule;
use PhpParser\Node;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;

class BooleanPropertyRule extends RecursivePropertiesRule
{
    private PropertyNameGeneratorInterface $propertyNameGenerator;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly Container $container,
    ) {
        $this->propertyNameGenerator = $this->container->getService('propertyNameGenerator');
    }

    public function filterBySchemaAttribute(SchemaAttribute $schemaAttribute): bool
    {
        return $this->reflectionProvider->getClass($schemaAttribute->getClass())->isSubclassOf(JsonResource::class);
    }

    public function validateProperty(Property $property, Node\ArrayItem $node): array
    {
        $errors = [];

        if ($property->type === 'boolean' && !$this->propertyNameGenerator->isBooleanProperty($property->property)) {
            $errors[] = RuleErrorBuilder::message(sprintf('Schema property "%s" must start with "is" or "has"', $property->property))
                ->identifier(RuleIdentifier::identifier('booleanInJsonResourceMustStartWithIs'))
                ->file($this->file)
                ->line($node->getLine())
                ->build();
        }

        return $errors;
    }
}
