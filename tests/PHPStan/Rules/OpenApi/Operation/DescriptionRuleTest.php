<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\DescriptionRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<DescriptionRule>
 */
class DescriptionRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new DescriptionRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/SummaryAndDescription.php',
            ],
            [
                ['Operation "DELETE /method1" description is too short, must be at least 20 chars', 16],
                ['Operation "DELETE /method2" description is too short, must be at least 20 chars', 25],
                ['Operation "GET /class" description is too short, must be at least 20 chars', 9],
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
