<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApiTools\PHPStan\Collectors\JsonResourceToArrayReturnCollector;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Helpers\SchemaProperties;
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
readonly class MissingSchemaPropertyRule implements Rule
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

            /**
             * @var string $property
             * @var Node\ArrayItem $item
             */
            foreach ($this->getReturnStatementIterator($returnStatement) as [$property, $item]) {
                $schemaProperty = SchemaProperties::findByName($schema, $property);
                if ($schemaProperty === null) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Returned property "%s" is not defined in schema "%s" class', $property, $returnStatement->getClass()))
                        ->identifier(RuleIdentifier::identifier('missingJsonResourceSchemaProperty'))
                        ->file($returnStatement->getFile())
                        ->line($item->getLine())
                        ->build();
                }
            }
        }

        return $errors;
    }
}
