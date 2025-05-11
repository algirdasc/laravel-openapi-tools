<?php

declare(strict_types=1);

namespace PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\PathRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<PathRule>
 */
class PathRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new PathRule(
            reflectionProvider: $this->createReflectionProvider(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/PathDataClass.php',
            ],
            [
                ['Operation "DELETE method1/" path must start leading slash', 14],
                ['Operation "DELETE method1/" must not end with trailing slash', 14],
                ['Operation "DELETE /method2/{parameter}" parameter "parameter" is missing in operation parameters', 21],
                ['Operation "POST method4" path must start leading slash', 38],
                ['Operation "POST /method5/{parameter}" parameter "parameter" is missing in operation parameters', 45],
                ['Operation "GET /class/" must not end with trailing slash', 9],
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
