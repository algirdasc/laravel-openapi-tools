<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Get;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\NodeHelper;
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
readonly class RequestBodyFormRequestParametersRule implements Rule
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
                $errors[] = RuleErrorBuilder::message(sprintf('Operation attribute applied to class, but "%s" method not found', $operationAttribute->getMethod()))
                    ->identifier(RuleIdentifier::identifier('operationMethodNotFound'))
                    ->file($operationAttribute->getFile())
                    ->line($operationAttribute->getAttribute()->getLine())
                    ->build();

                continue;
            }

            $operation = $operationAttribute->getOperation();

            foreach ($methodReflection->getParameters() as $parameter) {
                $type = $parameter->getType();
                if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                    continue;
                }

                $parameterReflection = $this->reflectionProvider->getClass($type->getName());
                if (!$parameterReflection->isSubclassOf(FormRequest::class)) {
                    continue;
                }

                $requestBody = !Generator::isDefault($operation->requestBody) ? $operation->requestBody : null;

                // TODO: check reference

                if ($requestBody === null && !$operation instanceof Get) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Missing "requestBody" property for method "%s" with FormRequest parameter type "%s"', $methodReflection->getName(), $parameter->getName()))
                        ->identifier(RuleIdentifier::identifier('missingRequestBodyOnFormRequestMethod'))
                        ->file($operationAttribute->getFile())
                        ->line($operationAttribute->getAttribute()->getLine())
                        ->build();
                }
            }
        }

        return $errors;
    }
}
