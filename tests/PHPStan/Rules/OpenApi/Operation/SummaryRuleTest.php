<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\SummaryRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<SummaryRule>
 */
class SummaryRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new SummaryRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/SummaryAndDescription.php',
            ],
            [
                ['Operation "DELETE /method1" summary is too short, must be at least 10 chars', 17],
                ['Operation "DELETE /method2" summary is too long, must be at to 64 chars', 26],
                ['Operation "GET /class" summary is too short, must be at least 10 chars', 10],
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
