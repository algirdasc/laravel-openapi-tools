<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Parameter;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\Attributes;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class PathParameterValidator implements ValidatorInterface
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function validate(Operation $operation): array
    {
        $errors = [];

        $path = $operation->path;
        $parameters = is_array($operation->parameters) ? $operation->parameters : [];

        preg_match_all('/\{([\w\:]+?)\??\}/', $path, $pathParameters);
        foreach ($pathParameters[1] as $pathParameter) {
            $found = false;

            foreach ($parameters as $parameter) {
                if (!Generator::isDefault($parameter->ref)) {
                    $reflection = $this->reflectionProvider->getClass($parameter->ref)->getNativeReflection();
                    $attributes = Attributes::getAttributes($reflection, Parameter::class);

                    foreach ($attributes as $attribute) {
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
