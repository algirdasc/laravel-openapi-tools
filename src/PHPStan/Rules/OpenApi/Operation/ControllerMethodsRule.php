<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class ControllerMethodsRule implements Rule
{
    use IteratesOverCollection;

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        /** @var OperationAttribute $operationAttribute */
        foreach ($this->getIterator($node, [ClassOperationCollector::class]) as $operationAttribute) {
            /** @var ReflectionClass $classReflection */
            $classReflection = $this->reflectionProvider->getClass($operationAttribute->getClass())->getNativeReflection();

            $traitMethods = $this->getTraitMethodNames($classReflection);

            $methods = [];
            foreach ($classReflection->getMethods() as $method) {
                if (!$method->isPublic()) {
                    continue;
                }

                if (in_array($method->getName(), $traitMethods, true)) {
                    continue;
                }

                $methods[] = $method->getName();
            }

            if (in_array('__invoke', $methods) && count($methods) > 1) {
                $errors[] = RuleErrorBuilder::message('Controller must not have any other methods if "__invoke" method is defined')
                    ->identifier(RuleIdentifier::identifier('keepControllerCleanFromOtherMethods'))
                    ->file($operationAttribute->getFile())
                    ->line($operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * Collects the names of every method provided by the traits used by the
     * class (recursively, including nested traits and traits used by parent
     * classes), so they can be excluded from the "other methods" check.
     *
     * @return array<int, string>
     */
    private function getTraitMethodNames(ReflectionClass $classReflection): array
    {
        $names = [];

        foreach ($classReflection->getTraits() as $trait) {
            foreach ($trait->getMethods() as $method) {
                $names[] = $method->getName();
            }

            $names = [...$names, ...$this->getTraitMethodNames($trait)];
        }

        $parent = $classReflection->getParentClass();
        if ($parent !== false) {
            $names = [...$names, ...$this->getTraitMethodNames($parent)];
        }

        return $names;
    }
}
