<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Laravel\Resource;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\ClassSchemaCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\Laravel\Resource\BooleanPropertyRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\TagCountRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\RequiredPropertiesRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\SchemaNameRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;
use Tests\PHPStan\Rules\OpenApi\Operation\Collector;

/**
 * @extends CustomRuleTestCase<BooleanPropertyRule>
 */
class BooleanPropertyRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new BooleanPropertyRule(
            reflectionProvider: $this->createReflectionProvider(),
            container: $this->getContainer(),
        );
    }

    public function testRuleJsonResource(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/BooleanPropertyJsonResourceDataClass.php',
            ],
            [
                ['Schema property "boolean_2" must start with "is" or "has"', 11],
                ['Schema property "boolean_1" must start with "is" or "has"', 10],
            ]
        );
    }

    public function testRuleFormRequest(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/BooleanPropertyFormRequestDataClass.php',
            ],
            [
            ]
        );
    }

    protected function getCollectors(): array
    {
        return [
            new ClassSchemaCollector(
                reflectionProvider: $this->createReflectionProvider(),
            ),
        ];
    }
}
