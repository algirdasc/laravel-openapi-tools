<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\Laravel\FormRequest\Collectors;

use Illuminate\Foundation\Http\FormRequest;
use OpenApiTools\PHPStan\Traits\CollectsArrays;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\ShouldNotHappenException;

class FormRequestArrayCollector implements Collector
{
    use CollectsArrays;

    public function getNodeType(): string
    {
        return Node\Stmt\Return_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope)
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return null;
        }

        if (!$scope->getClassReflection()->isSubclassOf(FormRequest::class)) {
            return null;
        }

        $function = $scope->getFunction();
        if ($function === null || $function->getName() !== 'rules') {
            return null;
        }

        return $this->collectReturnArray($node, $scope);
    }
}
