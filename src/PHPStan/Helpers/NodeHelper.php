<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Helpers;

use PhpParser\Node;

class NodeHelper
{
    /**
     * @param array<Node\Arg|Node\VariadicPlaceholder> $args
     * @param string $name
     * @return Node\Expr|null
     */
    public static function findInArgsByName(array $args, string $name): ?Node\Expr
    {
        foreach ($args as $arg) {
            if ($arg instanceof Node\VariadicPlaceholder) {
                continue;
            }

            if ($arg->name?->name === $name) {
                return $arg->value;
            }
        }

        return null;
    }
}
