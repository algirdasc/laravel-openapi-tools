<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerMethodParametersRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<ControllerMethodParametersRule>
 */
class ControllerMethodParametersRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerMethodParametersRule(
            reflectionProvider: $this->createReflectionProvider(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/MethodParametersControllerDataClass.php',
            ],
            [
                ['Method "__invoke" parameter "parameter2" must be of type string', 8],
                ['Method "__invoke" parameter "parameter1" must be of type string', 8],
                ['Method "__invoke" parameters "parameter1", "parameter2" are either missing or not in the correct order', 8],
                ['Method "method1" parameter "parameter2" must be of type string', 17],
                ['Method "method1" parameter "parameter1" must be of type string', 17],
                ['Method "method1" parameters "parameter1", "parameter2" are either missing or not in the correct order', 17],
                ['Method "method2" parameters "parameter1" are either missing or not in the correct order', 24],
                ['Method "method3" parameters "parameter2" are either missing or not in the correct order', 31],
            ]
        );
    }

    public function testRuleWithoutInvoke(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/InvokeWithoutMethodControllerDataClass.php',
            ],
            [
                ['Operation attribute applied to class, but "__invoke" method not found', 7],
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
