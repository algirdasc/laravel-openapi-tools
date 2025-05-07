<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi;

abstract class AbstractOpenApiRule
{
    abstract public function getValidators(): array;
}
