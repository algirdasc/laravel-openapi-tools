<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\RequiredRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<RequiredRule>
 */
class RequiredRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequiredRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/ValidationRulesDataClass.php',
            ],
            [
                ['Property "required-property-1" is required in rules, but not in schema', 16],
                ['Property "required-property-2" is required in rules, but not in schema', 17],
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