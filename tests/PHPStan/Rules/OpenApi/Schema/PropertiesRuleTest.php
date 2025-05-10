<?php

declare(strict_types=1);

namespace PHPStan\Rules\OpenApi\Schema;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\ClassSchemaCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\TagCountRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\PropertiesRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\RequiredPropertiesRule;
use OpenApiTools\PHPStan\Rules\OpenApi\Schema\SchemaNameRule;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<PropertiesRule>
 */
class PropertiesRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new PropertiesRule(
            reflectionProvider: $this->createReflectionProvider(),
            container: $this->getContainer(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/InvalidPropertiesDataClass.php',
            ],
            [
                ['Property "property1" has incorrect type', 14],
                ['Property "number_property" has incorrect format', 17],
                ['Property "casedProperty" has incorrect case, expected "cased_property"', 18],
                ['Property "cased.property" has incorrect case, expected "cased_property"', 19],
                ['Property "cased property" has incorrect case, expected "cased_property"', 20],
                ['Property "some_date1_at" has must have "string" type', 21],
                ['Property "some_date2_at" has must have "date-time" format', 22],
                ['Property "referenced_property" reference "Tests\PHPStan\Rules\OpenApi\Schema\Data\SchemalessDataClass" does not have schema attribute', 23],
                ['Property "referenced_property2" reference "oops" does not exist', 24],
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
