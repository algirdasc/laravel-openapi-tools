<?php

declare(strict_types=1);

namespace PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerMethodsRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<ControllerMethodsRule>
 */
class ControllerMethodsRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ControllerMethodsRule(
            reflectionProvider: $this->createReflectionProvider(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/InvokeWithMethodsControllerDataClass.php',
                __DIR__ . '/Data/InvokeWithTraitControllerDataClass.php',
            ],
            [
                ['Controller must not have any other methods if "__invoke" method is defined', 8],
            ]
        );
    }

    protected function getCollectors(): array
    {
        return [
            new ClassOperationCollector(
                reflectionProvider: $this->createReflectionProvider(),
            ),
        ];
    }
}
