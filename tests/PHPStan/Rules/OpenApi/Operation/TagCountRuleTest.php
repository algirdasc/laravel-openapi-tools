<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<ControllerInvokeMethodRule>
 */
class TagCountRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerInvokeMethodRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/InvalidTagCount.php'
            ],
            [
                ['X should not be Y', 15],
            ]
        );
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/phpstan.neon'];
    }
}
