<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest;

use OpenApiTools\PHPStan\Collectors\FormRequestRulesReturnCollector;
use OpenApiTools\PHPStan\Rules\Laravel\FormRequest\MissingSchemaPropertiesRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<MissingSchemaPropertiesRule>
 */
class MissingSchemaPropertiesRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new MissingSchemaPropertiesRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/MissingSchemaPropertiesDataClass.php',
            ],
            [
                ['Missing schema properties on FormRequest "Tests\PHPStan\Rules\Laravel\FormRequest\Data\MissingSchemaPropertiesDataClass" class', 8],
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