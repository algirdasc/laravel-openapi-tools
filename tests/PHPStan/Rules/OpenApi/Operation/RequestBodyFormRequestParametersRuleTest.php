<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\RequestBodyFormRequestParametersRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<RequestBodyFormRequestParametersRule>
 */
class RequestBodyFormRequestParametersRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequestBodyFormRequestParametersRule(
            reflectionProvider: $this->createReflectionProvider(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/RequestBodyFormRequestDataClass.php',
            ],
            [
                ['Missing "requestBody" property for method "__invoke" with FormRequest parameter type "request"', 8],
                ['Missing "requestBody" property for method "method1" with FormRequest parameter type "request"', 17],
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
