<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Validators;

use Illuminate\Validation\Rule;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use OpenApiTools\PHPStan\DTO\ArrayReturn;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Helpers\SchemaProperties;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Generators\RuleGenerator;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\ValidatorInterface;
use PhpParser\Node;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class EnumRuleValidator implements ValidatorInterface
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
            $isEnumerable = false;
            if ($item->value instanceof Node\Expr\Array_) {
                foreach ($item->value->items as $rule) {
                    if (!$rule->value instanceof Node\Expr\StaticCall) {
                        continue;
                    }

                    if ($rule->value->class->name !== Rule::class && $rule->value->name->name !== 'in') {
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
                    ->file($arrayReturn->getFile())
                    ->line($item->value->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
