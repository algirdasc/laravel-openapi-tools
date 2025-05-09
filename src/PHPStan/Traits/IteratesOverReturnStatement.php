<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Traits;

use OpenApiTools\PHPStan\DTO\ReturnStatement;
use PhpParser\Node;

trait IteratesOverReturnStatement
{
    /**
     * @return iterable<array{0: string, 1: Node\Expr\ArrayItem}>
     */
    public function getReturnStatementIterator(ReturnStatement $returnStatement): iterable
    {
        foreach ($returnStatement->getItems() as $item) {
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
