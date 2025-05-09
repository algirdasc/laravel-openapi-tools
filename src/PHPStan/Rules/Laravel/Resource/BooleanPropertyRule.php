<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApi\Annotations\Property;
use OpenApiTools\PHPStan\Generators\PropertyNameGeneratorInterface;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\Abstract\RecursivePropertiesRule;
use PhpParser\Node;
use PHPStan\DependencyInjection\Container;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
class BooleanPropertyRule extends RecursivePropertiesRule implements Rule
{
    private PropertyNameGeneratorInterface $propertyNameGenerator;

    public function __construct(
        private readonly Container $container,
    ) {
        $this->propertyNameGenerator = $this->container->getService('propertyNameGenerator');
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
