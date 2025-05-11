<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Laravel\JsonResource;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\ClassSchemaCollector;
use OpenApiTools\PHPStan\Collectors\JsonResourceToArrayReturnCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\Laravel\JsonResource\BooleanPropertyRule;
use OpenApiTools\PHPStan\Rules\Laravel\JsonResource\MissingSchemaPropertiesRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\TagCountRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\RequiredPropertiesRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\SchemaNameRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;
use Tests\PHPStan\Rules\OpenApi\Operation\Collector;

/**
 * @extends CustomRuleTestCase<MissingSchemaPropertiesRule>
 */
class MissingSchemaPropertiesRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new MissingSchemaPropertiesRule(
            reflectionProvider: $this->createReflectionProvider(),
        );
    }

    public function testRuleProperties(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/MissingSchemaPropertiesObjectDataClass.php',
            ],
            [
                ['Missing schema properties on JsonResource "Tests\PHPStan\Rules\Laravel\JsonResource\Data\MissingSchemaPropertiesObjectDataClass" class', 8],
            ]
        );
    }

    public function testRuleItems(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/MissingSchemaPropertiesItemsDataClass.php',
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
