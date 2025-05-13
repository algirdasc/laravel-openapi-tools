<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\EnumRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<EnumRule>
 */
class EnumRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new EnumRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/EnumRulesDataClass.php',
            ],
            [
                ['Property "enum-property-1" is has enum values in rules, but not in schema', 23],
                ['Property "enum-property-2" is has enum values in rules, but not in schema', 24],
                ['Property "enum-property-3" is has enum values in rules, but not in schema', 25],
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