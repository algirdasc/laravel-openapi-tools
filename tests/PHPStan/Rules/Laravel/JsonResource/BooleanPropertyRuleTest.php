<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Laravel\JsonResource;

use OpenApiTools\PHPStan\Collectors\ClassSchemaCollector;
use OpenApiTools\PHPStan\Rules\Laravel\JsonResource\BooleanPropertyRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

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
                ['Schema property "boolean_1" must start with "is" or "has"', 11],
                ['Schema property "boolean_2" must start with "is" or "has"', 13],
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
