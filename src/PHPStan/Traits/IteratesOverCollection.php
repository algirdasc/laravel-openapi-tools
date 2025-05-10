<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Traits;

use PhpParser\Node;
use PHPStan\Collectors\Collector;
use PHPStan\Node\CollectedDataNode;

trait IteratesOverCollection
{
    /**
     * @template TNodeType of Node
     * @param array<class-string<Collector<TNodeType, mixed>>>|class-string<Collector<TNodeType, mixed>> $collectors
     * @return iterable<object>
     */
    public function getIterator(Node $node, array|string $collectors): iterable
    {
        /**
         * @phpstan-ignore phpstanApi.instanceofAssumption
         */
        if (!$node instanceof CollectedDataNode) {
            return;
        }

        if (!is_array($collectors)) {
            $collectors = [$collectors];
        }

        foreach ($collectors as $collector) {
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
}
