<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Generators;

use OpenApiTools\PHPStan\DTO\ArrayReturn;

class RuleGenerator
{
    public static function iterate(ArrayReturn $arrayReturn): iterable
    {
        foreach ($arrayReturn->getItems() as $item) {
            if ($item->unpack === true) {
                // we cannot unpack array items :(
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
