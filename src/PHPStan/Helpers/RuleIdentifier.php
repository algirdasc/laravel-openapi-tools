<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Helpers;

class RuleIdentifier
{
    public static function identifier(string $identifier): string
    {
        return sprintf('openApiTools.%s', $identifier);
    }
}
