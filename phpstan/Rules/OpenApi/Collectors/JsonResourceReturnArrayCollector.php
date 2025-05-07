<?php

declare(strict_types=1);

namespace Rules\Collectors;

use Illuminate\Http\Resources\Json\JsonResource;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\ShouldNotHappenException;

class JsonResourceReturnArrayCollector extends AbstractArrayCollector implements Collector
{
    public function getNodeType(): string
    {
        return Node\Stmt\Return_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return null;
        }

        if (!$scope->getClassReflection()->isSubclassOf(JsonResource::class)) {
            return null;
        }

        $function = $scope->getFunction();
        if ($function === null || $function->getName() !== 'toArray') {
            return null;
        }

        return $this->collectReturnArray($node, $scope);
    }
}
