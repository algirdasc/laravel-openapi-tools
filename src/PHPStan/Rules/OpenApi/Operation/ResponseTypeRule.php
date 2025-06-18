<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Annotations\Response;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use ReflectionNamedType;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class ResponseTypeRule implements Rule
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

            $returnType = $methodReflection->getReturnType();
            if (!$returnType instanceof ReflectionNamedType || $returnType->isBuiltin()) {
                // todo: add support for builtin return types
                continue;
            }

            $referenceFound = false;
            $hasDirectReference = false;

            $operation = $operationAttribute->getOperation();
            $responses = !Generator::isDefault($operation->responses) ? $operation->responses : [];
            foreach ($responses as $response) {
                $responseCode = (int) $response->response;

                if ($responseCode < 200 || $responseCode > 299) {
                    continue;
                }

                if (!$this->hasDirectReference($response)) {
                    continue;
                }

                $hasDirectReference = true;

                if ($this->isReferencedDirectly($response, $returnType->getName())) {
                    $referenceFound = true;
                    break;
                }
            }

            if ($hasDirectReference && !$referenceFound) {
                $errors[] = RuleErrorBuilder::message(sprintf('Method "%s" return type is not found in operation responses', $operationAttribute->getMethod()))
                    ->identifier(RuleIdentifier::identifier('returnTypeMismatch'))
                    ->file($operationAttribute->getFile())
                    ->line($operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }

    private function hasDirectReference(Response $response): bool
    {
        $responseReference = Generator::isDefault($response->ref)
            ? ($response->_unmerged[0]->ref ?? null)
            : $response->ref;

        return !Generator::isDefault($responseReference);
    }

    private function isReferencedDirectly(Response $response, string $returnType): bool
    {
        $responseReference = Generator::isDefault($response->ref)
            ? ($response->_unmerged[0]->ref ?? null)
            : $response->ref;

        return $responseReference === $returnType;
    }
}
