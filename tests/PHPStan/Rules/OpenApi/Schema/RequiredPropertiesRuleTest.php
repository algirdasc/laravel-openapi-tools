<?php

declare(strict_types=1);

namespace PHPStan\Rules\OpenApi\Schema;

use OpenApiTools\PHPStan\Rules\OpenApi\Schema\RequiredPropertiesRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;
use Tests\PHPStan\Rules\OpenApi\Operation\Collector;

/**
 * @extends CustomRuleTestCase<RequiredPropertiesRule>
 */
class RequiredPropertiesRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequiredPropertiesRule(
            reflectionProvider: $this->createReflectionProvider(),
            container: $this->getContainer(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/RequiredPropertiesDataClass.php',
            ],
            [
                ['Required properties "sub-property3", "item-property3", "property3" is not defined in properties', 07],
            ]
        );
    }

    public function testRuleWithoutSchema(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/SchemalessDataClass.php',
            ],
            [
            ]
        );
    }
}
