<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\MissingSchemaRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<MissingSchemaRule>
 */
class MissingSchemaRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new MissingSchemaRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/MissingSchemaDataClass.php',
            ],
            [
                ['Missing schema attribute on FormRequest "Tests\PHPStan\Rules\Laravel\FormRequest\Data\MissingSchemaDataClass" class', 7],
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