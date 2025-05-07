<?php

declare(strict_types=1);

namespace Rules\Rules;

use OpenApi\Annotations\Operation;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

/**
 * Rule checks for missing/redundant operation (get, post, patch,...) attribute
 *
 * @implements Rule<Stmt\Class_>
 */
readonly class ValidateOpenApiOperationRule implements Rule
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Stmt\Class_::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $className = (string) $node->namespacedName;

        $reflectionClass = $this->reflectionProvider->getClass($className)->getNativeReflection();
        $errors = $this->validateOpenApiAttributes($reflectionClass);

        foreach ($reflectionClass->getMethods() as $method) {
            $errors = [
                ...$errors,
                ...$this->validateOpenApiAttributes($method),
            ];
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    private function validateOpenApiAttributes(ReflectionClass|ReflectionMethod $reflection): array // @phpstan-ignore missingType.generics
    {
        $errors = [];
        foreach ($reflection->getAttributes() as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if (!$attributeInstance instanceof Operation) {
                continue;
            }

            $errors = $this->validateAttribute($attribute);
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    private function validateAttribute(ReflectionAttribute $attribute): array // @phpstan-ignore missingType.generics
    {
        $errors = [];

        $args = $attribute->getArguments();
        $path = $args['path'] ?? '';

        // check if path has all attributes required
        if (count($args['parameters'] ?? []) < substr_count($args['path'], '{')) {
            $errors[] = RuleErrorBuilder::message(sprintf('Documentation for "%s" missing parameters definitions', $path))
                ->identifier('openApiAttribute.pip')
                ->build();
        }

        if (count($args['tags'] ?? []) !== 1) {
            $errors[] = RuleErrorBuilder::message(sprintf('Documentation for "%s" must have exactly 1 tag', $path))
                ->identifier('openApiAttribute.incorrectTagCount')
                ->build();
        }

        if (strlen($args['summary'] ?? '') < 10) {
            $errors[] = RuleErrorBuilder::message(sprintf('Documentation for "%s" summary is too short', $path))
                ->identifier('openApiAttribute.summaryToShort')
                ->build();
        }

        if (strlen($args['summary'] ?? '') > 64) {
            $errors[] = RuleErrorBuilder::message(sprintf('Documentation for "%s" summary is too long', $path))
                ->identifier('openApiAttribute.summaryToLong')
                ->build();
        }

        if (strlen($args['description'] ?? '') < 20) {
            $errors[] = RuleErrorBuilder::message(sprintf('Documentation for "%s" description is too short', $path))
                ->identifier('openApiAttribute.descriptionToShort')
                ->build();
        }

        if (!str_starts_with($args['path'] ?? '', '/')) {
            $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must start with /', $path))
                ->identifier('openApi.operationPathDoesNotStartWithSlash')
                ->build();
        }

        if (str_ends_with($args['path'] ?? '', '/')) {
            $errors[] = RuleErrorBuilder::message(sprintf('Path "%s" must not end with trailing slash', $path))
                ->identifier('openApi.operationPathMustNotEndWithSlash')
                ->build();
        }

        return $errors;
    }
}
