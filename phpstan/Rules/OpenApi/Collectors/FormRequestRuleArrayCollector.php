<?php

declare(strict_types=1);

namespace Rules\Collectors;

use Illuminate\Foundation\Http\FormRequest;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\ShouldNotHappenException;

class FormRequestRuleArrayCollector extends AbstractArrayCollector implements Collector
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
