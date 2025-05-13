<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Attributes\Parameter;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\NodeHelper;
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
readonly class PathRule implements Rule
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
        if (!$node instanceof CollectedDataNode) {
            return [];
        }

        $errors = [];

        /** @var OperationAttribute $operationAttribute */
        foreach ($this->getIterator($node, [MethodOperationCollector::class, ClassOperationCollector::class]) as $operationAttribute) {
            $operation = $operationAttribute->getOperation();
            $operationName = sprintf('%s %s', strtoupper($operation->method), $operation->path);

            $pathNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'path');

            if (!str_starts_with($operation->path, '/')) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" path must start leading slash', $operationName))
                    ->identifier(RuleIdentifier::identifier('operationPathDoesNotStartWithSlash'))
                    ->file($operationAttribute->getFile())
                    ->line($pathNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            if (str_ends_with($operation->path, '/')) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" must not end with trailing slash', $operationName))
                    ->identifier(RuleIdentifier::identifier('operationPathMustNotEndWithSlash'))
                    ->file($operationAttribute->getFile())
                    ->line($pathNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            $parameters = !Generator::isDefault($operation->parameters) ? $operation->parameters : [];

            preg_match_all('/\{([\w\:]+?)\??\}/', $operation->path, $pathParameters);
            foreach ($pathParameters[1] as $pathParameter) {
                $found = false;

                foreach ($parameters as $parameter) {
                    if (!Generator::isDefault($parameter->ref) && is_string($parameter->ref)) {
                        /** @var ReflectionClass $reflection */
                        $reflection = $this->reflectionProvider->getClass($parameter->ref)->getNativeReflection();
                        $attribute = Attributes::getAttribute($reflection, Parameter::class);
                        if ($attribute !== null) {
                            /** @var Parameter $parameter */
                            $parameter = $attribute->newInstance();
                        }
                    }

                    if ($parameter->name === $pathParameter && $parameter->in === 'path') {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" parameter "%s" is missing in operation parameters', $operationName, $pathParameter))
                        ->identifier(RuleIdentifier::identifier('pathParameterMissingInSchemaParameters'))
                        ->file($operationAttribute->getFile())
                        ->line($pathNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                        ->build();
                }
            }
        }

        return $errors;
    }
}
