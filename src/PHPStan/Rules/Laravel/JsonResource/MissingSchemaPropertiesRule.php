<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\JsonResource;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassSchemaCollector;
use OpenApiTools\PHPStan\Collectors\JsonResourceToArrayReturnCollector;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use OpenApiTools\PHPStan\DTO\SchemaAttribute;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class MissingSchemaPropertiesRule implements Rule
{
    use IteratesOverCollection;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        /** @var SchemaAttribute $schemaAttribute */
        foreach ($this->getIterator($node, [ClassSchemaCollector::class]) as $schemaAttribute) {
            if (!$this->reflectionProvider->getClass($schemaAttribute->getClass())->isSubclassOf(JsonResource::class)) {
                continue;
            }

            $schema = $schemaAttribute->getSchema();

            if (!Generator::isDefault($schema->type) && $schema->type !== 'object') {
                return [];
            }

            if (!Generator::isDefault($schema->properties) && $schema->properties) {
                return [];
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Missing schema properties on JsonResource "%s" class', $schemaAttribute->getClass()))
                ->identifier(RuleIdentifier::identifier('missingJsonResourceSchemaProperties'))
                ->file($schemaAttribute->getFile())
                ->line($schemaAttribute->getAttribute()->getLine())
                ->build();
        }

        return $errors;
    }
}
