<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Laravel\JsonResource;

use OpenApiTools\PHPStan\Collectors\JsonResourceToArrayReturnCollector;
use OpenApiTools\PHPStan\Rules\Laravel\JsonResource\MissingSchemaPropertyRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;
use Tests\PHPStan\Rules\OpenApi\Operation\Collector;

/**
 * @extends CustomRuleTestCase<MissingSchemaPropertyRule>
 */
class MissingSchemaPropertyRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new MissingSchemaPropertyRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/MissingReturnPropertyDataClass.php',
            ],
            [
                ['Returned property "return-key1" is not defined in schema "Tests\PHPStan\Rules\Laravel\JsonResource\Data\MissingReturnPropertyDataClass" class', 20],
            ]
        );
    }

    protected function getCollectors(): array
    {
        return [
            new JsonResourceToArrayReturnCollector(),
        ];
    }
}
