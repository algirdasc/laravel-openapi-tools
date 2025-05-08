<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Annotations\Operation;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;

interface ValidatorInterface
{
    /**
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array;
}
