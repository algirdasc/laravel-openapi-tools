<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\Resource;

use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\JsonResourceToArrayReturnCollector;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class MissingSchemaPropertiesRule implements Rule
{
    use IteratesOverCollection;

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

            if (!Generator::isDefault($schema->type) && $schema->type !== 'object') {
                return [];
            }

            if (!Generator::isDefault($schema->properties) && !empty($schema->properties)) {
                return [];
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Missing schema properties on JsonResource "%s" class', $returnStatement->getClass()))
                ->identifier(RuleIdentifier::identifier('missingJsonResourceSchemaProperties'))
                ->file($returnStatement->getFile())
                ->line($returnStatement->getLine())
                ->build();
        }

        return $errors;
    }
}
