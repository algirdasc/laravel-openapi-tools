<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ControllerInvokeMethodRule>
 */
class ControllerInvokeMethodValidatorTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerInvokeMethodRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Data/OperationAttributeOnInvokeMethod.php'], [
            [
                'X should not be Y', 15,
            ],
        ]);
    }
}
