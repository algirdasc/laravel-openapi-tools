<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Traits;

use OpenApi\Attributes\Schema;
use OpenApiTools\PHPStan\DTO\ReturnStatement;
use OpenApiTools\PHPStan\Helpers\Attributes;
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

        if ($scope->getParentScope() !== null) {
            return null;
        }



        /** @var ReflectionClass $reflection */
        $reflection = $classReflection = $scope->getClassReflection()?->getNativeReflection();
        /** @var Schema|null $schema */
        $schema = Attributes::getAttribute($reflection, Schema::class)?->newInstance();

        $collectedData = new ReturnStatement(
            class: $classReflection->getName(),
            file: $classReflection->getFileName() ?: $classReflection->getName(),
            line: $classReflection->getStartLine(),
            schema: $schema,
        );

        // if return is not array or current scope has parent scope (sub-return), skip
        if (!$node->expr instanceof Node\Expr\Array_) {
            return serialize($collectedData);
        }

        $collectedData->setItems($node->expr->items);

        return serialize($collectedData);
    }
}
