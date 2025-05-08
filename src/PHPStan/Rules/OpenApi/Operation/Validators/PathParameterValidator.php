<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Parameter;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use Throwable;

readonly class PathParameterValidator implements ValidatorInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];

        $path = $operation->path;
        $parameters = !Generator::isDefault($operation->parameters) ? $operation->parameters : [];

        preg_match_all('/\{([\w\:]+?)\??\}/', $path, $pathParameters);
        foreach ($pathParameters[1] as $pathParameter) {
            $found = false;

            foreach ($parameters as $parameter) {
                if (!Generator::isDefault($parameter->ref) && is_string($parameter->ref)) {
                    try {
                        /** @var ReflectionClass $reflection */
                        $reflection = $this->reflectionProvider->getClass($parameter->ref)->getNativeReflection();
                        $attributes = Attributes::getAttributes($reflection, Parameter::class);
                    } catch (Throwable) {
                        $attributes = [];
                    }

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
                $errors[] = RuleErrorBuilder::message(sprintf('Path parameter "%s" is missing in operation "%s" parameters', $pathParameter, $path))
                    ->identifier(RuleIdentifier::identifier('pathParameterMissingInSchemaParameters'))
                    ->build();
            }
        }

        return $errors;
    }
}
