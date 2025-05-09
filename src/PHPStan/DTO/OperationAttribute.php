<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\DTO;

use OpenApi\Annotations\Operation;
use PhpParser\Node\Attribute;

readonly class OperationAttribute
{
    public function __construct(
        private string $class,
        private string $file,
        private Operation $operation,
        private Attribute $attribute
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getOperation(): Operation
    {
        return $this->operation;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}
