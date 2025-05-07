<?php

declare(strict_types=1);

namespace Rules\Collectors;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use ReflectionClass;

abstract class AbstractArrayCollector
{
    protected function collectReturnArray(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return null;
        }

        /** @var ReflectionClass $classReflection */
        $classReflection = $scope->getClassReflection()->getNativeReflection();

        $collectedData = [
            'class' => $classReflection->getName(),
            'line' => $classReflection->getStartLine(),
            'is_parent_scoped' => $scope->getParentScope() !== null,
            'items' => [],
        ];

        // if return is not array or current scope has parent scope (sub-return), skip
        if (!$node->expr instanceof Node\Expr\Array_) {
            return serialize($collectedData);
        }

        $collectedData['items'] = $node->expr->items;

        return serialize($collectedData);
    }
}
