<?php

declare(strict_types=1);

namespace PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\RequestBodyReferenceRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<RequestBodyReferenceRule>
 */
class RequestBodyReferenceRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequestBodyReferenceRule(
            reflectionProvider: $this->createReflectionProvider(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/RequestBodyReferenceDataClass.php',
            ],
            [
                ['RequestBody reference "Tests\PHPStan\Rules\OpenApi\Schema\Data\SchemalessDataClass" does not have schema attribute', 18],
                ['RequestBody reference "something" does not exist', 28],
                ['RequestBody reference "Tests\PHPStan\Rules\OpenApi\Schema\Data\SchemalessDataClass" does not have schema attribute', 10],
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
