<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
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
readonly class RequiredRule implements Rule
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

            $required = !Generator::isDefault($schema->required) ? $schema->required : [];

            /**
             * @var string $property
             * @var Node\ArrayItem $item
             */
            foreach ($this->getReturnStatementIterator($returnStatement) as [$property, $item]) {
                $isRequired = false;
                if ($item->value instanceof Node\Expr\Array_) {
                    foreach ($item->value->items as $rule) {
                        if ($rule->value instanceof Node\Scalar\String_ && $rule->value->value === 'required') {
                            $isRequired = true;
                            break;
                        }
                    }
                } elseif ($item->value instanceof Node\Scalar\String_) {
                    $isRequired = str_contains($item->value->value, 'required');
                }

                if ($isRequired && !in_array($property, $required, true)) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" is required in rules, but not in schema', $property))
                        ->identifier(RuleIdentifier::identifier('requestPropertyRequiredInRulesButNotInSchema'))
                        ->file($returnStatement->getFile())
                        ->line($item->value->getLine())
                        ->build();
                }
            }
        }

        return $errors;
    }
}
