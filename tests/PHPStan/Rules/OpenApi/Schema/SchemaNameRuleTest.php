<?php

declare(strict_types=1);

namespace PHPStan\Rules\OpenApi\Schema;

use OpenApiTools\PHPStan\Rules\OpenApi\Schema\SchemaNameRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;
use Tests\PHPStan\Rules\OpenApi\Operation\Collector;

/**
 * @extends CustomRuleTestCase<SchemaNameRule>
 */
class SchemaNameRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new SchemaNameRule(
            reflectionProvider: $this->createReflectionProvider(),
            container: $this->getContainer(),
        );
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/InvalidSchemaNameDataClass.php',
                __DIR__ . '/Data/SchemalessDataClass.php',
            ],
            [
                ['Schema name "" does not match "Tests.PHPStan.Rules.OpenApi.Schema.Data.InvalidSchemaNameDataClass"', 07],
            ]
        );
    }
}
