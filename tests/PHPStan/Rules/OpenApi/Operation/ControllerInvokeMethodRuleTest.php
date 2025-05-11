<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<ControllerInvokeMethodRule>
 */
class ControllerInvokeMethodRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerInvokeMethodRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/InvokeControllerDataClass.php',
            ],
            [
                ['OpenApi attributes must be applied on class scope', 9],
            ]
        );
    }

    protected function getCollectors(): array
    {
        return [
            new MethodOperationCollector(
                reflectionProvider: $this->createReflectionProvider(),
            ),
            new ClassOperationCollector(
                reflectionProvider: $this->createReflectionProvider(),
            ),
        ];
    }
}
