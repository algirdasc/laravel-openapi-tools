<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\DTO;

use PhpParser\Node\Attribute;

class SchemaAttribute
{
    public function __construct(
        private readonly string $class,
        private readonly string $file,
        private readonly int    $line,
        private ?Attribute $attribute = null
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

    public function getLine(): int
    {
        return $this->line;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }
}
