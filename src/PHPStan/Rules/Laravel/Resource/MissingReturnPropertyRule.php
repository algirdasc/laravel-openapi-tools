<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\JsonResourceToArrayReturnCollector;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use OpenApiTools\PHPStan\Traits\IteratesOverReturnStatement;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class MissingReturnPropertyRule implements Rule
{
    use IteratesOverCollection;
    use IteratesOverReturnStatement;

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

        /** @var ReturnStatement $returnStatement */
        foreach ($this->getIterator($node, [JsonResourceToArrayReturnCollector::class]) as $returnStatement) {
            $schema = $returnStatement->getSchema();
            if ($schema === null) {
                continue;
            }

            if (Generator::isDefault($schema->properties) ||!$schema->properties || !$returnStatement->getItems()) {
                return [];
            }

            $returnedProperties = $schemaProperties = [];

            /**
             * @var string $property
             * @var Node\ArrayItem $item
             */
            foreach ($this->getReturnStatementIterator($returnStatement) as [$property, $item]) {
                $returnedProperties[$property] = (int) $item->key?->getLine();
            }

            foreach ($schema->properties as $propertySchema) {
                $schemaProperties[$propertySchema->property] = 0;
            }

            $returnDiff = array_diff_key($schemaProperties, $returnedProperties);
            foreach ($returnDiff as $property => $line) {
                $errors[] = RuleErrorBuilder::message(sprintf('Schema property "%s" is not returned in JsonResource "%s" class', $property, $returnStatement->getClass()))
                    ->identifier(RuleIdentifier::identifier('missingJsonResourceReturnProperty'))
                    ->file($returnStatement->getFile())
                    ->line($line)
                    ->build();
            }
        }

        return $errors;
    }
}
