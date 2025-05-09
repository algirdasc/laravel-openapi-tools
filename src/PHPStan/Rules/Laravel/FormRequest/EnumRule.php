<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest;

use OpenApi\Generator;
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
readonly class EnumRule implements Rule
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
                $isEnumerable = false;
                if ($item->value instanceof Node\Expr\Array_) {
                    foreach ($item->value->items as $rule) {
                        if (!$rule->value instanceof Node\Expr\StaticCall) {
                            continue;
                        }

                        $class = $rule->value->class;
                        $method = $rule->value->name;

                        if (!$class instanceof Node\Name || !$method instanceof Node\Identifier) {
                            continue;
                        }

                        if ($class->name !== \Illuminate\Validation\Rule::class && $method->name !== 'in') {
                            continue;
                        }

                        $isEnumerable = true;
                        break;
                    }
                }

                $schemaProperty = SchemaProperties::findByName($schema, $property);

                if ($isEnumerable && $schemaProperty !== null && Generator::isDefault($schemaProperty->enum)) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Property "%s" is has enum values in rules, but not in schema', $property))
                        ->identifier(RuleIdentifier::identifier('requestPropertyEnumInRulesButNotInSchema'))
                        ->file($returnStatement->getFile())
                        ->line($item->value->getLine())
                        ->build();
                }
            }
        }

        return $errors;
    }
}
