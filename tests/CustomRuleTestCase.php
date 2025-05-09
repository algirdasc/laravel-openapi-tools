<?php

declare(strict_types=1);

namespace Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @template TRule of Rule
 * @extends RuleTestCase<TRule>
 */
abstract class CustomRuleTestCase extends RuleTestCase
{
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../extension.neon'];
    }
}
