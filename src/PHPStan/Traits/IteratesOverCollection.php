<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Traits;

use PhpParser\Node;
use PHPStan\Collectors\Collector;

trait IteratesOverCollection
{
    /**
     * @param class-string<Collector> $collector
     */
    public function getIterator(Node $node, string $collector): iterable
    {
        $collectedData = $node->get($collector);
        foreach ($collectedData as $declarations) {
            foreach ($declarations as $declaration) {
                if ($declaration === null) {
                    continue;
                }

                yield unserialize($declaration);
            }
        }
    }
}
