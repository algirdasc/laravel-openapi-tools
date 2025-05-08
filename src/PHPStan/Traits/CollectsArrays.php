<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Traits;

use OpenApiTools\PHPStan\DTO\ArrayReturn;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\ShouldNotHappenException;

trait CollectsArrays
{
    /**
     * @throws ShouldNotHappenException
     */
    private function collectReturnArray(Node $node, Scope $scope): ?string
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return null;
        }

        /** @var ReflectionClass|null $classReflection */
        $classReflection = $scope->getClassReflection()?->getNativeReflection();
        if ($classReflection === null) {
            throw new ShouldNotHappenException();
        }

        $collectedData = new ArrayReturn(
            class: $classReflection->getName(),
            file: $classReflection->getFileName() ?: $classReflection->getName(),
            line: $classReflection->getStartLine(),
            isParentScoped: $scope->getParentScope() !== null,
        );

        // if return is not array or current scope has parent scope (sub-return), skip
        if (!$node->expr instanceof Node\Expr\Array_) {
            return serialize($collectedData);
        }

        $collectedData->setItems($node->expr->items);

        return serialize($collectedData);
    }
}
