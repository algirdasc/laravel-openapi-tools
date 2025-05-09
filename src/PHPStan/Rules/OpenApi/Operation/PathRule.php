<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Attributes\Parameter;
use OpenApi\Generator;
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
        foreach ($this->getIterator($node, MethodOperationCollector::class) as $operationAttribute) {
            $operation = $operationAttribute->getOperation();
            $pathNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'path');

            if (!str_starts_with($operation->path, '/')) {
                $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must start with /', $operation->path))
                    ->identifier(RuleIdentifier::identifier('operationPathDoesNotStartWithSlash'))
                    ->file($operationAttribute->getFile())
                    ->line($pathNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            if (str_ends_with($operation->path, '/')) {
                $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must not end with trailing slash', $operation->path))
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
                        $attributes = Attributes::getAttributes($reflection, Parameter::class);

                        foreach ($attributes as $attribute) {
                            /** @var Parameter $parameter */
                            $parameter = $attribute->newInstance();
                            break;
                        }
                    }

                    if ($parameter->name === $pathParameter && $parameter->in === 'path') {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Path parameter "%s" is missing in operation "%s" parameters', $pathParameter, $operation->path))
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
