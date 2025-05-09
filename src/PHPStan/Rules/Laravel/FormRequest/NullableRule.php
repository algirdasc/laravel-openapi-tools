<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
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
readonly class NullableRule implements Rule
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
        foreach ($this->getIterator($node, [FormRequestRulesReturnCollector::class]) as $returnStatement) {
            $schema = $returnStatement->getSchema();
            if ($schema === null) {
                continue;
            }

            /**
             * @var string $property
             * @var Node\ArrayItem $item
             */
            foreach ($this->getReturnStatementIterator($returnStatement) as [$property, $item]) {
                $isNullable = false;
                if ($item->value instanceof Node\Expr\Array_) {
                    foreach ($item->value->items as $rule) {
                        if ($rule->value instanceof Node\Scalar\String_ && $rule->value->value === 'nullable') {
                            $isNullable = true;
                            break;
                        }
                    }
                } elseif ($item->value instanceof Node\Scalar\String_) {
                    $isNullable = str_contains($item->value->value, 'nullable');
                }

                $schemaProperty = SchemaProperties::findByName($schema, $property);

                if ($isNullable && $schemaProperty?->nullable !== true) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" is nullable in rules, but not in schema', $property))
                        ->identifier(RuleIdentifier::identifier('requestPropertyNullableInRulesButNotInSchema'))
                        ->file($returnStatement->getFile())
                        ->line($item->value->getLine())
                        ->build();
                }
            }
        }

        return $errors;
    }
}
