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
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use Throwable;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class ControllerMethodParametersValidator implements Rule
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
        foreach ($this->getIterator($node, [ClassOperationCollector::class, MethodOperationCollector::class]) as $operationAttribute) {
            /** @var ReflectionClass $classReflection */
            $classReflection = $this->reflectionProvider->getClass($operationAttribute->getClass())->getNativeReflection();

            try {
                $methodReflection = $classReflection->getMethod($operationAttribute->getMethod());
            } catch (\ReflectionException $e) {
                return [
                    RuleErrorBuilder::message('Operation attribute applied to class, but "%s" method not found')
                        ->identifier(RuleIdentifier::identifier('operationMethodNotFound'))
                        ->file($operationAttribute->getFile())
                        ->line($operationAttribute->getAttribute()->getLine())
                        ->build()
                ];
            }

            $operation = $operationAttribute->getOperation();

            $orderedParameters = [];
            foreach ($methodReflection->getParameters() as $parameter) {
                $type = $parameter->getType();
                if (!$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
                    continue;
                }

                if ($type->getName() !== 'string') {
                    $errors[] = RuleErrorBuilder::message(sprintf('Method "%s" parameter "%s" must be of type string', $methodReflection->getName(), $parameter->getName()))
                        ->identifier(RuleIdentifier::identifier('incorrectMethodParametersType'))
                        ->file($operationAttribute->getFile())
                        ->line($operationAttribute->getAttribute()->getLine())
                        ->build();

                    continue;
                }

                $orderedParameters[] = $parameter->getName();
            }

            if (!$orderedParameters) {
                return $errors;
            }

            preg_match_all('/{(.*?)}/', $operation->path, $pathParameters);
            $pathParameters = $pathParameters[1];

            $parametersDiff = array_diff_assoc($pathParameters, $orderedParameters);
            if ($parametersDiff) {
                $errors[] = RuleErrorBuilder::message(sprintf('Method "%s" parameters "%s" are either missing or not in the correct order', $methodReflection->getName(), implode(', ', $parametersDiff)))
                    ->identifier(RuleIdentifier::identifier('missingOrIncorrectMethodParametersOrder'))
                    ->file($operationAttribute->getFile())
                    ->line($operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            $parametersDiff = array_diff_assoc($orderedParameters, $pathParameters);
            if ($parametersDiff) {
                $errors[] = RuleErrorBuilder::message(sprintf('Method "%s" parameters "%s" are missing in operation path', $methodReflection->getName(), implode(', ', $parametersDiff)))
                    ->identifier(RuleIdentifier::identifier('missingMethodParameterInSchemaPath'))
                    ->file($operationAttribute->getFile())
                    ->line($operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
