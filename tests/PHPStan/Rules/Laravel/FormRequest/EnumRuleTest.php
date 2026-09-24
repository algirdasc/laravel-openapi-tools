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
                ['Property "enum-property-1" is has enum values in rules, but not in schema', 27],
                ['Property "enum-property-2" is has enum values in rules, but not in schema', 28],
                ['Property "enum-property-3" is has enum values in rules, but not in schema', 29],
                ['Property "enum-property-4" is has enum values in rules, but not in schema', 33],
                ['Property "enum-property-5" is has enum values in rules, but not in schema', 34],
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