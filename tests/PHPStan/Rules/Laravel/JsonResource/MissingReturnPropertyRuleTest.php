<?php

declare(strict_types=1);

namespace Tests\PHPStan\Rules\Laravel\JsonResource;

use OpenApiTools\PHPStan\Collectors\JsonResourceToArrayReturnCollector;
use OpenApiTools\PHPStan\Rules\Laravel\JsonResource\MissingReturnPropertyRule;
use PHPStan\Rules\Rule;
use Tests\CustomRuleTestCase;

/**
 * @extends CustomRuleTestCase<MissingReturnPropertyRule>
 */
class MissingReturnPropertyRuleTest extends CustomRuleTestCase
{
    protected function getRule(): Rule
    {
        return new MissingReturnPropertyRule();
    }

    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Data/MissingReturnPropertyDataClass.php',
            ],
            [
                ['Schema property "schema-key2" is not returned in JsonResource "Tests\PHPStan\Rules\Laravel\JsonResource\Data\MissingReturnPropertyDataClass" class', 8],
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
