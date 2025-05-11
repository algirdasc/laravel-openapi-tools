<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\NullableRule;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\RequiredRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<NullableRule>
 */
class NullableRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new NullableRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/ValidationRulesDataClass.php',
            ],
            [
                ['Property "nullable-property-1" is nullable in rules, but not in schema', 18],
                ['Property "nullable-property-2" is nullable in rules, but not in schema', 19],
            ]
        );
    }

    protected function getCollectors(): array
    {
        return [
            new FormRequestRulesReturnCollector(),
        ];
    }
}