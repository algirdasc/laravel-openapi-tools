<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\TagCountRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<TagCountRule>
 */
class TagCountRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new TagCountRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/TagCountDataClass.php',
            ],
            [
                ['Operation "DELETE /method1-tags" must have at least 1 tag', 15],
                ['Operation "POST /method2-tags" must have at least 1 tag', 21],
                ['Operation "GET /class-tags" must have at least 1 tag', 9],
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
