<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Generators;

use OpenApiTools\PHPStan\DTO\ReturnStatement;
use PhpParser\Node;

class RuleGenerator
{
    /**
     * @return iterable<array{0: string, 1: Node\Expr\ArrayItem}>
     */
    public static function iterate(ReturnStatement $arrayReturn): iterable
    {
        foreach ($arrayReturn->getItems() as $item) {
            if ($item->unpack === true) {
                // we cannot unpack array items :(
                continue;
            }

            if (!$item->key instanceof Node\Scalar\String_) {
                continue;
            }

            $property = $item->key->value;
            if (str_contains($property, '.')) {
                // we do not support nested properties :(
                continue;
            }

            yield [$property, $item];
        }
    }
}
