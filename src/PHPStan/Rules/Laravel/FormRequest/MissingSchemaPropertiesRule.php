<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
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
        foreach ($this->getIterator($node, [FormRequestRulesReturnCollector::class]) as $returnStatement) {
            $schema = $returnStatement->getSchema();
            if ($schema === null) {
                continue;
            }

            if (!Generator::isDefault($schema->properties) && $schema->properties) {
                continue;
            }

            $file = $returnStatement->getFile();

            $errors[$file] = RuleErrorBuilder::message(sprintf('Missing schema properties on FormRequest "%s" class', $returnStatement->getClass()))
                ->identifier(RuleIdentifier::identifier('missingRequestSchemaProperties'))
                ->file($returnStatement->getFile())
                ->line($returnStatement->getLine())
                ->build();
        }

        return array_values($errors);
    }
}
