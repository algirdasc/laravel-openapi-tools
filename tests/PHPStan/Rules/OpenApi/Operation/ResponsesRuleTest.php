<?php

declare(strict_types=1);

namespace PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ResponsesRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<ResponsesRule>
 */
class ResponsesRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResponsesRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/ResponsesDataClass.php',
            ],
            [
                ['Operation "GET /method1" must have success response set', 16],
                ['Operation "GET /method1" must have unauthorized response set', 16],
                ['Operation "GET /method1" must have error response set', 16],
                ['Operation "GET /method1" must have unprocessable response set', 16],
                ['Operation "GET /method2" must have success response set', 22],
                ['Operation "GET /method2" must have unauthorized response set', 22],
                ['Operation "GET /method2" must have error response set', 22],
                ['Operation "POST /method3" must have unprocessable response set', 32],
                ['Operation "GET /class" must have success response set', 9],
                ['Operation "GET /class" must have unauthorized response set', 9],
                ['Operation "GET /class" must have error response set', 9],
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
