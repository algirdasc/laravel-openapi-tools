<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Annotations\Operation;
use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class ControllerMethodsValidator implements Rule
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

            $methods = [];
            foreach ($classReflection->getMethods() as $method) {
                if (!$method->isPublic()) {
                    continue;
                }

                $methods[] = $method->getName();
            }

            if (in_array('__invoke', $methods) && count($methods) > 1) {
                return [
                    RuleErrorBuilder::message('Controller must not have any other methods if __invoke is defined')
                        ->identifier(RuleIdentifier::identifier('keepControllerCleanFromOtherMethods'))
                        ->file($operationAttribute->getFile())
                        ->line($operationAttribute->getAttribute()->getLine())
                        ->build(),
                ];
            }
        }

        return $errors;
    }
}
