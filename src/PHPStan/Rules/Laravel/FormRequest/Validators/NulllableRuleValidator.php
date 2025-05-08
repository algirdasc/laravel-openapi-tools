<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Validators;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Helpers\SchemaProperties;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Generators\RuleGenerator;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\ValidatorInterface;
use PhpParser\Node;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class NulllableRuleValidator implements ValidatorInterface
{
    /**
     * @throws ShouldNotHappenException
     */
    public function validate(ArrayReturn $arrayReturn, ?Schema $schema): array
    {
        if ($schema === null) {
            return [];
        }

        $errors = [];

        /**
         * @var string $property
         * @var Node\ArrayItem $item
         */
        foreach (RuleGenerator::iterate($arrayReturn) as [$property, $item]) {
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
                    ->file($arrayReturn->getFile())
                    ->line($item->value->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
