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
                        if ($rule->value instanceof Node\Expr\StaticCall) {
                            $isEnumerable = $this->isEnumRuleStaticCall($rule->value);
                        }

                        if ($rule->value instanceof Node\Scalar\String_) {
                            $isEnumerable = $this->isEnumRuleString($rule->value);
                        }

                        if ($isEnumerable) {
                            break;
                        }
                    }
                } elseif ($item->value instanceof Node\Scalar\String_) {
                    $isEnumerable = $this->isEnumRuleString($item->value);
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

    private function isEnumRuleStaticCall(Node\Expr\StaticCall $call): bool
    {
        $class = $call->class;
        $method = $call->name;

        if (!$class instanceof Node\Name || !$method instanceof Node\Identifier) {
            return false;
        }

        if ($class->name !== \Illuminate\Validation\Rule::class && $method->name !== 'in') {
            return false;
        }

        return true;
    }

    private function isEnumRuleString(Node\Scalar\String_ $string): bool
    {
        if (str_contains($string->value, '|')) {
            $rules = explode('|', $string->value);
        } else {
            $rules = [$string->value];
        }

        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'in:')) {
                return true;
            }
        }

        return false;
    }
}

